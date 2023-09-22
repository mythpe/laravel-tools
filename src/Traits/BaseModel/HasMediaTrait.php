<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\BaseModel;

use Illuminate\Support\Str;
use Spatie\Image\Exceptions\InvalidManipulation;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileCannotBeAdded;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileDoesNotExist;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\MediaLibrary\MediaCollections\Exceptions\InvalidBase64Data;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait HasMediaTrait
{
    use InteractsWithMedia;

    /**
     * @var string
     */
    public static string $mediaSingleCollection = 'avatar';
    /**
     * @var string
     */
    public static string $mediaAttachmentsCollection = 'attachments';
    /**
     * Auto Responsive Images of media single collection
     *
     * @var bool
     */
    public bool $singleMediaUsingResponsiveImages = !1;
    /**
     * Disabled auto media thumbnail
     *
     * @var bool
     */
    public bool $singleMediaUsingThumb = !1;
    /**
     * Name of media conversion to be used
     *
     * @var string
     */
    public string $singleMediaThumbName = 'thumb';
    /**
     * media single collection thumb width
     *
     * @var int
     */
    public int $singleMediaThumbWidth = 250;
    /**
     * media single collection thumb height
     *
     * @var int
     */
    public int $singleMediaThumbHeight = 250;

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(static::$mediaSingleCollection)->withResponsiveImagesIf($this->singleMediaUsingResponsiveImages)->singleFile();
    }

    /**
     * @throws InvalidManipulation
     */
    public function registerMediaConversions(Media $media = null): void
    {
        if ($this->singleMediaUsingThumb) {
            $this->addMediaConversion($this->singleMediaThumbName)
                ->performOnCollections($this->getSingleMediaThumbPerformOnCollections())
                ->width($this->singleMediaThumbWidth)
                ->height($this->singleMediaThumbHeight);
        }
    }

    /**
     * Name of collection to register with performOnCollections
     *
     * @return string
     */
    public function getSingleMediaThumbPerformOnCollections(): string
    {
        return static::$mediaSingleCollection;
    }

    /**
     * @param array|string[]|string|UploadedFile $files
     * @param null $collection
     *
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws InvalidBase64Data
     */
    public function addModelMedia($files, $collection = null): void
    {
        if (!is_array($files)) {
            $files = [$files];
        }
        //d($files);
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }
            if (is_string($file)) {
                if (isBase64($file)) {
                    $media = $this->addMediaFromBase64($file);
                }
                elseif (filter_var($file, FILTER_VALIDATE_URL)) {
                    $media = $this->addMediaFromUrl($file);
                }
                else {
                    $media = is_file($file) ? (Str::startsWith($file, base_path()) ? $this->copyMedia($file) : $this->addMedia($file)) : $this->addMediaFromRequest($file);
                }
            }
            else {
                $media = $this->addMedia($file);
            }
            $media->toMediaCollection($collection ?: static::$mediaSingleCollection);
        }
    }

    /**
     * @param null $collection
     * @param string $conversionName
     *
     * @return string|null
     */
    public function getModelThumbUrl($collection = null, string | null $conversionName = null): ?string
    {
        if ($this->singleMediaUsingThumb) {
            $conversionName = $conversionName ?: $this->singleMediaThumbName;
            return $this->getModelMediaUrl($collection, $conversionName);
        }
        return $this->getModelMediaUrl($collection, $conversionName ?: '');
    }

    /**
     * @param null $collection
     * @param string $conversionName
     *
     * @return string|null
     */
    public function getModelMediaUrl($collection = null, string $conversionName = ''): ?string
    {
        return $this->getModelMedia($collection ?: static::$mediaSingleCollection)?->getFullUrl($conversionName) ?: null;
    }

    /**
     * @param null $collection
     *
     * @return Media|null
     */
    public function getModelMedia($collection = null): ?Media
    {
        if (!$this->exists) {
            return null;
        }
        return $this->getFirstMedia($collection ?: static::$mediaSingleCollection);
    }

    /**
     * Get Responsive images of collection as array
     *
     * @param string|null $collection
     *
     * @return array
     */
    public function getModelResponsiveUrls(string | null $collection = null): array
    {
        $srcset = [];
        if ($this->singleMediaUsingResponsiveImages) {
            $collection = $collection ?: static::$mediaSingleCollection;
            $firstMedia = $this->getFirstMedia($collection);
            foreach ($firstMedia->getResponsiveImageUrls() as $url) {
                $a = explode('_', Str::beforeLast($url, '.'));
                $srcset[] = $url." {$a[count($a) - 2]}w";
            }
        }
        return $srcset;
    }

    /**
     * Get Main media as media file download
     *
     * @param null $collection
     *
     * @return string
     */
    public function getDownloadMediaFileUrl($collection = null): ?string
    {
        if (($media = $this->getModelMedia($collection))) {
            return downloadMedia($media);
        }
        return null;
    }

    /**
     * @param null $collection
     * @param string $conversionName
     *
     * @return string|null
     */
    public function getModelMediaBase64($collection = null, string $conversionName = ''): ?string
    {
        $collection = $collection ?: static::$mediaSingleCollection;
        if (($r = $this->getModelMedia($collection))) {
            $path = $r->getPath($conversionName);
            $type = pathinfo($path, PATHINFO_EXTENSION);
            $data = file_get_contents($path);
            return 'data:application/'.$type.';base64,'.base64_encode($data);
        }

        return null;
    }

    /**
     * @param string $requestKey
     * @param string|null $description
     * @param string|null $collection
     * @param array $properties
     *
     * @return Media
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     */
    public function addAttachment(string $requestKey, ?string $description = null, ?string $collection = null, array $properties = []): Media
    {
        $collection = $collection ?: static::$mediaAttachmentsCollection;
        $user = $properties['user_id'] ?? null;
        if ($user && !isset($properties['user_id'])) {
            $properties['user_id'] = $user;
        }
        if ($description && !isset($properties['description'])) {
            $properties['description'] = $description;
        }
        $mediaClass = config('media-library.media_model');
        $_media = new $mediaClass();
        $fillable = $_media->getFillable();
        $fill = [];
        foreach ($fillable as $k) {
            if (isset($properties[$k]) && ($v = $properties[$k])) {
                $fill[$k] = $v;
            }
            unset($properties[$k]);
        }
        $media = is_file($requestKey) ? (Str::startsWith($requestKey, base_path()) ? $this->copyMedia($requestKey) : $this->addMedia($requestKey)) : $this->addMediaFromRequest($requestKey);
        $media = $media->withCustomProperties($properties)->toMediaCollection($collection);
        $fill = array_merge($fill, request()->only($media->getFillable()));
        !empty($fill) && $media->fill($fill)->save();
        return $media;
    }
}

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
     * @var bool
     */
    public bool $registerMediaConversionsUsingModelInstance = !0;

    /**
     * Name of media conversion to be used
     * Example: [conversionName => ['responsive' => true, 'width' => 100, 'height' => 100, 'collection' => ['avatar']]]
     * Example: ['conversion1', 'conversion2']: will use getMediaPerformOnCollections method
     * @return array<string|int,string|array>
     */
    public static function getModelMediaConversions(): array
    {
        // return [static::$mediaSingleCollection];
        // return [static::$mediaSingleCollection => ['responsive' => !0]];
        return [];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection(static::$mediaSingleCollection)->singleFile();
    }

    /**
     * @param Media|null $media
     * @return void
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $defCollections = $this->getMediaPerformOnCollections();
        foreach (static::getModelMediaConversions() as $name => $options) {
            $conversionName = is_array($options) ? $name : $options;
            $width = is_array($options) ? ($options['width'] ?? null) : null;
            $height = is_array($options) ? ($options['height'] ?? null) : null;
            $collection = is_array($options) ? ($options['collection'] ?? $defCollections) : $defCollections;
            $format = is_array($options) ? ($options['format'] ?? 'webp') : 'webp';
            $conversion = $this->addMediaConversion($conversionName)->performOnCollections(...$collection);
            $withResponsiveImages = is_array($options) ? ($options['responsive'] ?? !1) : !1;
            if ($withResponsiveImages) {
                $conversion->withResponsiveImages();
            }
            if ($width) {
                $conversion->width($width);
            }
            if ($height) {
                $conversion->width($height);
            }
            if ($format) {
                $conversion->format($format);
            }
        }
    }

    /**
     * Name of collections to register with performOnCollections
     *
     * @return string[]
     */
    public function getMediaPerformOnCollections(): array
    {
        return [static::$mediaSingleCollection];
    }

    /**
     * @param array|string[]|string|UploadedFile $files
     * @param null $collection
     * @return Media[]
     * @throws FileCannotBeAdded
     * @throws FileDoesNotExist
     * @throws FileIsTooBig
     * @throws InvalidBase64Data
     */
    public function addModelMedia($files, $collection = null): array
    {
        if (!is_array($files)) {
            $files = [$files];
        }
        $append = [];
        foreach ($files as $file) {
            if (!$file) {
                continue;
            }
            if (is_string($file)) {
                if (filter_var($file, FILTER_VALIDATE_URL)) {
                    $media = $this->addMediaFromUrl($file);
                }
                elseif (is_file($file)) {
                    $media = Str::startsWith($file, base_path()) ? $this->copyMedia($file) : $this->addMedia($file);
                }
                elseif (request()->hasFile($file)) {
                    $media = $this->addMediaFromRequest($file);
                }
                else {
                    // isBase64($file);
                    $media = $this->addMediaFromBase64($file);
                }
            }
            else {
                $media = $this->addMedia($file);
            }
            $append[] = $media->toMediaCollection($collection ?: static::$mediaSingleCollection);
        }
        return $append;
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
        #Todo: Get responsive images
        // if ($this->singleMediaUsingResponsiveImages) {
        //     $collection = $collection ?: static::$mediaSingleCollection;
        //     $firstMedia = $this->getFirstMedia($collection);
        //     foreach ($firstMedia->getResponsiveImageUrls() as $url) {
        //         $a = explode('_', Str::beforeLast($url, '.'));
        //         $srcset[] = $url." {$a[count($a) - 2]}w";
        //     }
        // }
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

<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaFile extends Media
{
    public const CONST_EXCEL_EXT = ['xlsx', 'xlsm', 'xltx', 'xltm', 'xls', 'xlt', 'csv'];
    public const CONST_PDF_TYPE = 'pdf';
    public const CONST_EXCEL_TYPE = 'excel';
    public const CONST_IMAGE_TYPE = 'image';
    public const CONST_AUDIO_TYPE = 'audio';
    public const CONST_VIDEO_TYPE = 'video';
    public const CONST_PDF_MIMES = [
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'applications/vnd.pdf',
    ];
    /**
     * @var string[]
     */
    protected $appends = [
        'original_url',
        'preview_url',
        'file_name',
        'type',
        'mime_type',
        'size',
        'collection_name',
        'order_column',
        'model_type_to_string',
        'type_to_string',
        'size_to_string',
        'is_image',
        'is_pdf',
        'is_excel',
        'is_video',
        'is_audio',
        'description',
        'attachment_type',
        'collection_to_string',
        'download_url',
        'url',
    ];

    /**
     * @return string
     */
    public static function getModelTable(): string
    {
        return (new static)->getTable();
    }

    /**
     * @param $size
     *
     * @return string
     */
    public static function getSizeToString($size = null): string
    {
        if ($size) {
            if ($size > (1024 * 1024)) {
                return (string) __('replace.mb', ['name' => round(($size / 1024) / 1024, 2)]);
            }
            else {
                return (string) __('replace.kb', ['name' => round(($size / 1024), 2)]);
            }
        }
        return '';
    }

    /**
     * $this->type
     *
     * @return Attribute
     */
    protected function type(): Attribute
    {
        return Attribute::get(function () {
            $type = $this->getTypeFromExtension();
            if ($this->is_excel) {
                return static::CONST_EXCEL_TYPE;
            }

            if ($this->is_pdf) {
                return static::CONST_PDF_TYPE;
            }

            if ($this->is_video) {
                return static::CONST_VIDEO_TYPE;
            }

            if ($this->is_audio) {
                return static::CONST_AUDIO_TYPE;
            }

            if ($type !== static::TYPE_OTHER) {
                return $type;
            }

            return $this->getTypeFromMime();
        });
    }

    /**
     * $this->model_type_to_string
     *
     * @return Attribute
     */
    protected function modelTypeToString(): Attribute
    {
        return Attribute::get(function () {
            $name = class_basename($this->model_type);
            $type = Str::of($name)->pluralStudly()->studly();
            $trans = trans_has($k = "choice.$type") ? trans_choice($k, 1) : $this->model_type;
            return str_without_the($trans);
        });
    }

    /**
     * $this->type_to_string
     *
     * @return Attribute
     */
    protected function typeToString(): Attribute
    {
        return Attribute::get(function () {
            return trans_has($k = "const.media_types.$this->type") ? __($k) : (trans_has($k = "attributes.$this->type") ? __($k) : ucfirst($this->type));
        });
    }

    /**
     * $this->size_to_string
     *
     * @return Attribute
     */
    protected function sizeToString(): Attribute
    {
        return Attribute::get(function () {
            return static::getSizeToString($this->size ?: 0);
        });
    }

    /**
     * @return Attribute
     */
    protected function isImage(): Attribute
    {
        return Attribute::get(function () {
            return Str::startsWith($this->mime_type, static::CONST_IMAGE_TYPE);
        });
    }

    /**
     * @return Attribute
     */
    protected function isPdf(): Attribute
    {
        return Attribute::get(function () {
            $ext = strtolower($this->extension ?: '');
            return $ext == static::CONST_PDF_TYPE && in_array($this->mime_type, static::CONST_PDF_MIMES);
        });
    }

    /**
     * @return Attribute
     */
    protected function isExcel(): Attribute
    {
        return Attribute::get(function () {
            $ext = strtolower($this->extension ?: '');
            $type = $this->getTypeFromExtension();
            return in_array($ext, static::CONST_EXCEL_EXT) && strtolower($type) === static::TYPE_OTHER;
        });
    }

    /**
     * @return Attribute
     */
    protected function isVideo(): Attribute
    {
        return Attribute::get(function () {
            return Str::startsWith($this->mime_type, static::CONST_VIDEO_TYPE);
        });
    }

    /**
     * @return Attribute
     */
    protected function isAudio(): Attribute
    {
        return Attribute::get(function () {
            return Str::startsWith($this->mime_type, static::CONST_AUDIO_TYPE);
        });
    }

    /**
     * @return Attribute
     */
    protected function description(): Attribute
    {
        return Attribute::get(
            fn() => $this->getAttrToString($this->custom_properties['description'] ?? null)
        );
        // return Attribute::get(function () {
        //     $attachmentType = $this->attachment_type;
        //     if (!empty($attachmentType)) {
        //         return $attachmentType;
        //     }
        //
        //     $collectionToString = $this->collection_to_string;
        //     if (!empty($collectionToString)) {
        //         return $collectionToString;
        //     }
        //
        //     $customProperties = $this->custom_properties ?: [];
        //     $string = $customProperties['description'] ?? null;
        //     if ($string) {
        //         if (trans_has(($k = "attributes.$string"))) {
        //             $string = __($k);
        //         }
        //         elseif (trans_has(($k = "media.$string"))) {
        //             $string = __($k);
        //         }
        //         return $string;
        //     }
        //
        //     return '';
        // });
    }

    /**
     * $this->attachment_type
     * @return Attribute
     */
    protected function attachmentType(): Attribute
    {
        return Attribute::get(
            fn() => $this->getAttrToString($this->custom_properties['attachment_type'] ?? null)
        );
        // return Attribute::get(function () {
        //     $customProperties = $this->custom_properties ?: [];
        //     $string = $customProperties['attachment_type'] ?? null;
        //     if ($string) {
        //         if (trans_has(($k = "attributes.$string"))) {
        //             $string = __($k);
        //         }
        //         elseif (trans_has(($k = "media.$string"))) {
        //             $string = __($k);
        //         }
        //         return $string;
        //     }
        //     return '';
        // });
    }

    /**
     * $this->collection_to_string
     * @return Attribute
     */
    protected function collectionToString(): Attribute
    {
        return Attribute::get(
            fn() => $this->getAttrToString($this->collection_name) ?: ucfirst($this->collection_name ?: '')
        );
    }

    /**
     * @param mixed $string
     * @return string|null
     */
    protected function getAttrToString(mixed $string): ?string
    {
        if (empty($string)) {
            return null;
        }
        if (is_string($string)) {
            if (trans_has(($string))) {
                $string = __($string);
            }
            elseif (trans_has(($k = "attributes.$string"))) {
                $string = __($k);
            }
            elseif (trans_has(($k = "media.$string"))) {
                $string = __($k);
            }
            elseif (trans_has(($k = "labels.$string"))) {
                $string = __($k);
            }
        }
        return $string ?: null;
    }

    /**
     * $this->download_url
     * @return Attribute
     */
    protected function downloadUrl(): Attribute
    {
        return Attribute::get(function () {
            return downloadMedia($this);
        });
    }

    /**
     * $this->url
     * @return Attribute
     */
    protected function url(): Attribute
    {
        return Attribute::get(function () {
            return $this->getFullUrl();
        });
    }

}

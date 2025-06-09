<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2025 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property string $type
 * @property-read string $model_type_to_string
 * @property-read string $type_to_string
 * @property-read string $size_to_string
 * @property-read bool $is_image
 * @property-read bool $is_pdf
 * @property-read bool $is_excel
 * @property-read bool $is_video
 * @property-read bool $is_audio
 * @property-read string $description
 * @property-read string $attachment_type
 * @property-read string $collection_to_string
 */
class MediaFile extends Media
{
    public const EXCEL_EXT = ['xlsx', 'xlsm', 'xltx', 'xltm', 'xls', 'xlt', 'csv'];
    public const TYPE_PDF = 'pdf';
    public const TYPE_EXCEL = 'excel';
    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';
    public const PDF_MIMES = [
        'application/pdf',
        'application/x-pdf',
        'application/acrobat',
        'applications/vnd.pdf',
    ];
    protected $appends = [
        'original_url',
        'preview_url',
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
     * @return string
     */
    public function getTypeAttribute(): string
    {
        $type = $this->getTypeFromExtension();
        if ($this->is_excel) {
            return static::TYPE_EXCEL;
        }

        if ($this->is_pdf) {
            return static::TYPE_PDF;
        }

        if ($this->is_video) {
            return static::TYPE_VIDEO;
        }

        if ($this->is_audio) {
            return static::TYPE_AUDIO;
        }

        if ($type !== static::TYPE_OTHER) {
            return $type;
        }

        return $this->getTypeFromMime();
    }

    /**
     * $this->size_to_string
     *
     * @return string
     */
    public function getSizeToStringAttribute(): string
    {
        return static::getSizeToString($this->size ?: 0);
    }

    /**
     * $this->model_type_to_string
     *
     * @return string
     */
    public function getModelTypeToStringAttribute(): string
    {
        $name = class_basename($this->model_type);
        $type = Str::of($name)->pluralStudly()->studly();
        $trans = trans_has($k = "choice.$type") ? trans_choice($k, 1) : $this->model_type;
        return str_without_the($trans);
    }

    /**
     * $this->type_to_string
     *
     * @return string
     */
    public function getTypeToStringAttribute(): string
    {
        return trans_has($k = "const.media_types.$this->type") ? __($k) : (trans_has($k = "attributes.$this->type") ? __($k) : ucfirst($this->type));
    }

    /**
     * @return Attribute
     */
    protected function isImage(): Attribute
    {
        return Attribute::get(
            function () {
                return Str::startsWith($this->mime_type, static::TYPE_IMAGE);
            }
        );
    }

    /**
     * @return Attribute
     */
    protected function isPdf(): Attribute
    {
        return Attribute::get(
            function () {
                $ext = strtolower($this->extension ?: '');
                return $ext == static::TYPE_PDF && in_array($this->mime_type, static::PDF_MIMES);
            }
        );
    }

    /**
     * @return Attribute
     */
    protected function isExcel(): Attribute
    {
        return Attribute::get(
            function () {
                $ext = strtolower($this->extension ?: '');
                $type = $this->getTypeFromExtension();
                return in_array($ext, static::EXCEL_EXT) && strtolower($type) === static::TYPE_OTHER;
            }
        );
    }

    /**
     * @return Attribute
     */
    protected function isVideo(): Attribute
    {
        return Attribute::get(
            function () {
                return Str::startsWith($this->mime_type, static::TYPE_VIDEO);
            }
        );
    }

    /**
     * @return Attribute
     */
    protected function isAudio(): Attribute
    {
        return Attribute::get(
            function () {
                return Str::startsWith($this->mime_type, static::TYPE_AUDIO);
            }
        );
    }

    protected function description(): Attribute
    {
        return Attribute::get(
            function () {
                $attachmentType = $this->attachment_type;
                if (!empty($attachmentType)) {
                    return $attachmentType;
                }

                $collectionToString = $this->collection_to_string;
                if (!empty($collectionToString)) {
                    return $collectionToString;
                }

                $customProperties = $this->custom_properties ?: [];
                $string = $customProperties['description'] ?? null;
                if ($string) {
                    if (trans_has(($k = "attributes.$string"))) {
                        $string = __($k);
                    }
                    elseif (trans_has(($k = "media.$string"))) {
                        $string = __($k);
                    }
                    return $string;
                }

                return '';
            }
        );
    }

    protected function attachmentType(): Attribute
    {
        return Attribute::get(
            function () {
                $customProperties = $this->custom_properties ?: [];
                $string = $customProperties['attachment_type'] ?? null;
                if ($string) {
                    if (trans_has(($k = "attributes.$string"))) {
                        $string = __($k);
                    }
                    elseif (trans_has(($k = "media.$string"))) {
                        $string = __($k);
                    }
                    return $string;
                }
                return '';
            }
        );
    }

    protected function collectionToString(): Attribute
    {
        return Attribute::get(
            function () {
                $string = $this->collection_name;
                if ($string) {
                    if (trans_has(($k = "attributes.$string"))) {
                        $string = __($k);
                    }
                    elseif (trans_has(($k = "media.$string"))) {
                        $string = __($k);
                    }
                    return $string;
                }
                return '';
            }
        );
    }
}

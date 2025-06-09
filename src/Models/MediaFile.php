<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2025 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Models;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property string $type
 * @property-read string $model_type_to_string
 * @property-read string $type_to_string
 * @property-read string $size_to_string
 */
class MediaFile extends Media
{
    public const EXCEL_EXT = ['xlsx', 'xlsm', 'xltx', 'xltm', 'xls', 'xlt', 'csv'];
    public const TYPE_PDF = 'pdf';
    public const TYPE_EXCEL = 'excel';
    public const TYPE_IMAGE = 'image';
    public const TYPE_AUDIO = 'audio';
    public const TYPE_VIDEO = 'video';

    protected $appends = ['original_url', 'preview_url', 'model_type_to_string', 'type_to_string', 'size_to_string'];

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
        if (in_array(strtolower($this->extension ?: ''), static::EXCEL_EXT) && strtolower($type) === static::TYPE_OTHER) {
            return static::TYPE_EXCEL;
        }

        if (strtolower($this->extension ?: '') == static::TYPE_PDF) {
            return static::TYPE_PDF;
        }

        if (Str::contains($this->mime_type, static::TYPE_VIDEO)) {
            return static::TYPE_VIDEO;
        }

        if (Str::contains($this->mime_type, static::TYPE_AUDIO)) {
            return static::TYPE_AUDIO;
        }
        return $this->type()->get;
        // if ($type !== static::TYPE_OTHER) {
        //     return $type;
        // }
        //
        // return $this->getTypeFromMime();
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
        return trans_has($k = "attributes.$this->type") ? __($k) : ucfirst($this->type);
    }

}

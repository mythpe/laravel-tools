<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Illuminate\Support\Str;

trait HasRefKeyTrait
{
    /**
     * @return string
     */
    public static function getRefKeyName(): string
    {
        return 'ref_key';
    }

    /**
     * @return mixed|void
     */
    protected static function bootHasRefKeyTrait()
    {
        static::created(function (self $model) {
            $attribute = static::getRefKeyName();
            if (is_null($model->{$attribute})) {
                $model->{$attribute} = $model->generateRefKey();
                $model->save();
            }
        });
    }

    /**
     * @return string
     */
    public function generateRefKey(): string
    {
        if (!$this->id) {
            return '';
        }
        $prefix = $this->getRefKeyPrefix();
        $prefix .= now()->format('ynd');
        return $prefix.str_pad($this->id, $this->getRefKeyLength(), $this->getRefKeyPadString(), STR_PAD_LEFT);
    }

    /**
     * @return string
     */
    public function getRefKeyPrefix(): string
    {
        $class = class_basename(static::class);
        $class = Str::kebab($class);
        $class = ucwords($class, '-');
        $str = [];
        foreach (explode('-', $class) as $value) {
            $str[] = strtoupper(substr($value, 0, 1));
        }
        return implode('', $str);
    }

    /**
     * @return int
     */
    public function getRefKeyLength(): int
    {
        return 4;
    }

    /**
     * @return string
     */
    public function getRefKeyPadString(): string
    {
        return '0000';
    }
}

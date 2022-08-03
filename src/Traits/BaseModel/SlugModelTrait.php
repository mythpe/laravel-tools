<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\BaseModel;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\URL;

trait SlugModelTrait
{
    /**
     * Decode model id
     *
     * @param  string  $slug
     *
     * @return array
     */
    public static function decodeModelSlug(string $slug): array
    {
        return json_decode(decrypt(urldecode($slug)), true);
    }

    /**
     * @param $id
     * @param  false  $decode
     *
     * @return mixed|string|null
     */
    public static function printSlug($id, bool $decode = false)
    {
        if ($decode) {
            try {
                $s = urldecode($id);
                return json_decode(decrypt($s), true);
                // return json_decode(base64_decode($s), true);
            }
            catch (Exception $exception) {
                if (config('app.debug')) {
                    d($exception);
                }
            }
            return null;
        }
        else {
            $array = [static::class, $id];
            $s = encrypt(json_encode($array, JSON_UNESCAPED_UNICODE));
            return urlencode($s);
        }
    }

    /**
     * Encode model id
     *
     * @param  string|Model  $model
     *
     * @return string
     */
    public static function encodeModelSlug($model): string
    {
        $id = $model instanceof Model ? $model->id : $model;
        $array = [static::class, $id];
        $s = encrypt(json_encode($array));
        return urlencode($s);
    }

    /**
     * @param  string  $route
     * @param  int  $minutes
     * @param  array  $params
     *
     * @return string
     */
    public function getSignedModelSlugUrl(string $route, int $minutes = 30, array $params = []): string
    {
        return URL::temporarySignedRoute($route, now()->addMinutes($minutes), array_merge([
            $this->getModelSlug(),
            'locale' => auth()->check() ? (auth()->user()->locale ?: app()->getLocale()) : app()->getLocale(),
        ], $params));
    }

    /**
     * Get model id to hash
     *
     * @return string
     */
    public function getModelSlug(): string
    {
        return static::encodeModelSlug($this);
    }
}

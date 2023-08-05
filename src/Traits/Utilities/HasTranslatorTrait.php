<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslatorTrait
{
    /**
     * Append attributes on resource
     * @var bool
     */
    static bool $autoAppendTranslators = !0;

    /**
     * Attributes that have translation
     * @return string[]
     */
    public static function availableTranslationAttributes(): array
    {
        return ['name'];
    }

    /**
     * Available Locales of translator
     * @return array
     */
    public static function getAvailableTranslatorLocales(): array
    {
        return config('4myth-tools.locales');
    }

    /**
     * The primary locale of the translator
     * default application fallback local
     * @return string
     */
    public static function translatorLocale(): string
    {
        return config('app.fallback_locale');
    }

    /**
     * The default locale that will be used for translation
     * @return string
     */
    public static function defaultTranslatorLocale(): string
    {
        return app()->getLocale();
    }

    protected static function bootHasTranslatorTrait(): void
    {
        // $original = self::getOriginalTranslatorLocale();
        // $locales = self::getTranslatorLocales();
        // self::retrieved(function (self $model) use ($locales, $original) {
        //     if ($model::$autoAppendTranslators) {
        //     }
        // });
        self::saving(function (self $model) {
            $translatable = self::availableTranslationAttributes();
            if (empty($translatable)) {
                return;
            }
            $locales = self::getAvailableTranslatorLocales();
            $request = request()->all();
            $originalLocale = self::translatorLocale();
            foreach ($locales as $locale) {
                foreach ($translatable as $attribute) {
                    $key = "{$attribute}_$locale";
                    $value = $request[$key];
                    if ($locale == $originalLocale && $model->isFillable($attribute)) {
                        $model->fill([$attribute => $value]);
                    }
                }
            }
        });
        self::saved(function (self $model) {
            $translatable = self::availableTranslationAttributes();
            if (empty($translatable)) {
                return;
            }
            $locales = self::getAvailableTranslatorLocales();
            $request = request()->all();
            $originalLocale = self::translatorLocale();
            foreach ($locales as $locale) {
                foreach ($translatable as $attribute) {
                    $key = "{$attribute}_$locale";
                    $value = $request[$key];
                    if ($locale == $originalLocale && $model->isFillable($attribute)) {
                        // d($locale,$originalLocale);
                        continue;
                    }
                    if (array_key_exists($key, $request)) {
                        $model->createTranslation($locale, $attribute, $value);
                    }
                }
            }
        });
        self::deleted(function (self $model) {
            try {
                $model->translator()->delete();
            }
            catch (Exception) {
            }
        });
    }

    /**
     * Create attribute translation
     * Insert/Update translation of attribute
     * @param $locale
     * @param $attribute
     * @param $value
     * @return Model
     */
    public function createTranslation($locale, $attribute, $value): Model
    {
        $data = [
            'locale'    => $locale,
            'attribute' => $attribute,
            'value'     => $value,
        ];
        if (($model = $this->translator()->firstWhere(['locale' => $locale, 'attribute' => $attribute,]))) {
            $model->update(['value' => $value,]);
            return $model;
        }
        return $this->translator()->create($data);
    }

    /**
     * @return MorphMany
     */
    public function translator(): MorphMany
    {
        return $this->morphMany(config('4myth-tools.translatable_class'), config('4myth-tools.translatable_morph'));
    }

    /**
     * Get translation attributes
     * @param string|null $locale
     * @return array
     */
    public function translationAttributes(?string $locale = null): array
    {
        $result = [];
        $availableLocales = collect(static::getAvailableTranslatorLocales());
        $attributes = static::availableTranslationAttributes();
        if (!is_null($locale)) {
            $availableLocales = $availableLocales->filter(fn($e) => $e != $locale)->values();
        }
        foreach ($availableLocales as $availableLocale) {
            foreach ($attributes as $attribute) {
                if ($availableLocale == app()->getLocale() && !array_key_exists($attribute, $result)) {
                    $result[$attribute] = $this->{$attribute};
                }
                $result["{$attribute}_$availableLocale"] = $this->translateAttribute($attribute, $availableLocale);
            }
        }
        return $result;
    }

    /**
     * @param string|null $locale
     * @param string|null $attribute
     * @return MorphMany
     */
    public function translationQuery(?string $locale = null, ?string $attribute = null): MorphMany
    {
        return $this->translator()
            ->when(!is_null($locale), fn(Builder $q) => $q->where(['locale' => $locale]))
            ->when(!is_null($attribute), fn(Builder $q) => $q->where(['attribute' => $attribute]));
    }

    /**
     * translate attributes, if it doesn't have translation will be returned the original attribute
     * @param string $attribute
     * @param string|null $locale
     * @return string|null
     */
    public function translateAttribute(string $attribute, ?string $locale = null): ?string
    {
        return $this->translationModel($attribute, $locale)?->value ?: $this->{$attribute};
    }

    /**
     * Get translation model
     * @param string $attribute
     * @param string|null $locale
     * @return Model|null
     */
    public function translationModel(string $attribute, ?string $locale = null): ?Model
    {
        $locale = $locale ?: self::defaultTranslatorLocale();
        return $this->translationQuery($locale, $attribute)->first();
    }

    /**
     * Check if attribute has translation
     * @param string $attribute
     * @param string|null $locale
     * @return bool
     */
    public function hasTranslation(string $attribute, ?string $locale = null): bool
    {
        $locale = $locale ?: self::defaultTranslatorLocale();
        return $this->translationQuery($locale, $attribute)->exists();
    }
}
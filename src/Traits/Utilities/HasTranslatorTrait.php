<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasTranslatorTrait
{
    /**
     * Append Translators
     * @var bool
     */
    static bool $autoAppendTranslators = !0;

    protected static function bootHasTranslatorTrait(): void
    {
        // $original = self::getOriginalTranslatorLocale();
        // $locales = self::getTranslatorLocales();
        // self::retrieved(function (self $model) use ($locales, $original) {
        //     if ($model::$autoAppendTranslators) {
        //         $q = $model->getMapAllTranslators();
        //     }
        // });
        self::saved(function (self $model) {
            $translatable = self::getTranslatorAttributes();
            if (empty($translatable)) {
                return;
            }
            $locales = self::getTranslatorLocales();
            $request = request()->all();
            $originalLocale = self::getOriginalTranslatorLocale();
            foreach ($locales as $locale) {
                if ($locale == $originalLocale) {
                    continue;
                }
                foreach ($translatable as $attribute) {
                    $key = "{$attribute}_$locale";
                    if (array_key_exists($key, $request)) {
                        $model->createTranslator($locale, $attribute, $request[$key]);
                    }
                }
            }
        });
        self::deleted(function (self $model) {
            try {
                $model->translator()->delete();
            }
            catch (\Exception) {
            }
        });
    }

    public static function getTranslatorAttributes(): array
    {
        return ['name'];
    }

    public static function getTranslatorLocales(): array
    {
        return config('4myth-tools.locales');
    }

    public static function getOriginalTranslatorLocale(): string
    {
        return config('app.locale');
    }

    public function createTranslator($locale, $attribute, $value): Model
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

    public function translator(): MorphMany
    {
        return $this->morphMany(config('4myth-tools.translatable_class'), config('4myth-tools.translatable_morph'));
    }

    public function getMapTranslators(?string $locale = null): array
    {
        $locale = $locale ?: self::getDefualtTranslatorLocale();
        $result = [];
        foreach ($this->getTranslators($locale) as $translator) {
            $result["{$translator->attribute}_{$locale}"] = $translator->value;
        }
        return $result;
    }

    public static function getDefualtTranslatorLocale(): string
    {
        $locale = collect(self::getTranslatorLocales())->first(fn($l) => $l != self::getOriginalTranslatorLocale());
        return $locale ?: config('app.locale');
    }

    public function getTranslators(?string $locale = null): Collection
    {
        $locale = $locale ?: self::getDefualtTranslatorLocale();
        return $this->translator()->where(['locale' => $locale])->get();
    }

    public function translateAttribute(string $attribute, ?string $locale = null): ?string
    {
        return $this->getTranslatorModel($attribute, $locale)?->value;
    }

    public function getTranslatorModel(string $attribute, ?string $locale = null): ?Model
    {
        $locale = $locale ?: self::getDefualtTranslatorLocale();
        return $this->translator()->where(['locale' => $locale, 'attribute' => $attribute])->first();
    }

    public function getMapAllTranslators(): array
    {
        $result = [];
        foreach ($this->getAllTranslators() as $translator) {
            $locale = $translator->locale;
            $result["{$translator->attribute}_{$locale}"] = $translator->value;
        }
        return $result;
    }

    public function getAllTranslators(): Collection
    {
        return $this->translator()->get();
    }
}
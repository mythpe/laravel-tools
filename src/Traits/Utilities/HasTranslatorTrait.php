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
use Myth\LaravelTools\Models\Translator;

trait HasTranslatorTrait
{
    /**
     * Append attributes on resource
     * @var bool
     */
    static bool $enableAutoTranslation = !0;

    /**
     * cash translation attributes
     * @var array
     */
    protected array $translatedAttributes = [];

    /**
     * Attributes that have translation
     * @return string[]
     */
    public static function translatorAttributes(): array
    {
        return ['name'];
    }

    /**
     * The data will be used to save the translation of model. default request all
     * @return array
     */
    public static function getTranslationFrom(): array
    {
        return request()->all();
    }

    /**
     * @return bool
     */
    public static function autoTranslation(): bool
    {
        return static::$enableAutoTranslation;
    }

    /**
     * @return void
     */
    protected static function bootHasTranslatorTrait(): void
    {
        static::retrieved(function (self $model) {
            if (method_exists($model, 'autoTranslation') && $model->autoTranslation()) {
                $model->translatedAttributes = $model->translateAttributes();
                foreach ($model->translatedAttributes as $k => $v) {
                    if($v) {
                        $model->setAttribute($k, $v);
                        $model->append($k);
                    }
                }
            }
        });
        static::saving(function (self $model) {
            // Save the original value of attribute.
            $availableAttributes = static::translatorAttributes();
            if (empty($availableAttributes)) {
                return;
            }
            if (!empty($model->translatedAttributes)) {
                $rawAttributes = $model->getAttributes();
                foreach ($model->translatedAttributes as $k => $v) {
                    if (in_array($k, $availableAttributes)) {
                        continue;
                    }
                    unset($rawAttributes[$k]);
                }
                $model->setRawAttributes($rawAttributes);
            }
            $data = static::getTranslationFrom();
            $translatorLocale = Translator::getLocale();
            foreach (Translator::availableLocales() as $locale) {
                foreach ($availableAttributes as $attribute) {
                    $key = "{$attribute}_$locale";
                    $value = ($data[$key] ?? $model->{$attribute});
                    if ($locale == $translatorLocale && $model->isFillable($attribute)) {
                        // $model->fill([$attribute => $value]);
                        $model->setAttribute($attribute, $value);
                    }
                }
            }
        });
        static::saved(function (self $model) {
            $availableAttributes = static::translatorAttributes();
            if (empty($availableAttributes)) {
                return;
            }
            $data = static::getTranslationFrom();
            foreach (Translator::availableLocales() as $locale) {
                foreach ($availableAttributes as $attribute) {
                    $key = "{$attribute}_$locale";
                    $value = $data[$key] ?? null;
                    // Skip original attribute to save the translation
                    // if ($locale == $translatorLocale && $model->isFillable($attribute)) {
                    //     continue;
                    // }
                    if (array_key_exists($key, $data)) {
                        $model->createTranslation($locale, $attribute, $value);
                    }
                }
            }
            $model->translateAttributes();
        });
        static::deleted(function (self $model) {
            try {
                $model->translator()->delete();
            }
            catch (Exception) {
            }
        });
    }

    /**
     * Get translated attributes
     * @return array
     */
    public function getTranslatedAttributes(): array
    {
        return $this->translatedAttributes;
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
        return $this->morphMany(config('4myth-tools.translator_class'), config('4myth-tools.translator_morph'));
    }

    /**
     * Do & Get translation of attributes
     * @param string|null $locale
     * @return array
     */
    public function translateAttributes(?string $locale = null): array
    {
        $result = [];
        $availableLocales = collect(Translator::availableLocales());
        $attributes = static::translatorAttributes();
        if (!is_null($locale)) {
            $availableLocales = $availableLocales->filter(fn($e) => $e != $locale)->values();
        }
        foreach ($availableLocales as $availableLocale) {
            foreach ($attributes as $attribute) {
                $value = $this->translateAttribute($attribute, $availableLocale);
                if ($availableLocale == app()->getLocale() && !array_key_exists($attribute, $result)) {
                    $result[$attribute] = $value;
                }
                $result["{$attribute}_$availableLocale"] = $value;
            }
        }
        $this->translatedAttributes = $result;
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
        $locale = $locale ?: Translator::defaultLocale();
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
        $locale = $locale ?: Translator::defaultLocale();
        return $this->translationQuery($locale, $attribute)->exists();
    }
}
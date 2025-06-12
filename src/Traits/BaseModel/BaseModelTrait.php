<?php

namespace Myth\LaravelTools\Traits\BaseModel;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Myth\LaravelTools\Models\Translator;
use Myth\LaravelTools\Traits\Utilities\HasTranslatorTrait;
use Myth\LaravelTools\Utilities\Helpers;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait BaseModelTrait
{
    const HASH_PREFIX = 'MyTh';

    const HASH_DEFAULT_ID_LENGTH = 6;

    public ?int $numberFormat = 2;
    /** @var array<int,string> - e.g: ['customers','users'] */
    protected array $cloneRelations = [];


    /**
     * @return string
     */
    public static function getModelTable(): string
    {
        return (new static)->getTable();
    }

    /**
     * @return array
     */
    public static function getModelFillable(): array
    {
        return (new static)->getFillable();
    }

    /**
     * @param string|array $key
     * @param array $options
     * @return Collection
     */
    public static function langToCollection(string | array $key, array $options = []): Collection
    {
        $codes = is_array($key) ? $key : __($key);
        $values = [];
        $labels = $options['labels'] ?? [];
        $ids = $options['ids'] ?? [];
        foreach ($codes as $value => $name) {
            if (is_array($key)) {
                $value = $name;
                $name = trans_has($t = "attributes.{$name}") ? __($t) : $name;
            }
            $code = [
                'value' => $value,
                'label' => $name,
            ];
            foreach ($labels as $label) {
                $code[$label] = $name;
            }
            foreach ($ids as $id) {
                $code[$id] = $value;
            }

            $values[] = $code;
        }
        return collect($values);
    }

    /**
     * @return array
     */
    public static function getDaysOptions(): array
    {
        return collect(__('const.days'))->mapWithKeys(fn($v, $k) => [
            $k => ['value' => $k, 'label' => $v,],
        ])->values()->toArray();
    }

    /**
     * @return array
     */
    public static function getDaysArray(): array
    {
        return array_keys(__('const.days'));
    }

    public static function cloning($callback): void
    {
        static::registerModelEvent('cloning', $callback);
    }

    public static function cloned($callback): void
    {
        static::registerModelEvent('cloned', $callback);
    }

    /**
     * @param string|null $string
     * @param array $variables
     * @return string|null
     */
    public static function replaceVariables(?string $string = null, array $variables = []): ?string
    {
        if (!$string) {
            return null;
        }
        [$keys, $values] = $variables;
        return str_ireplace($keys, $values, $string);
    }

    /**
     * @param $hash
     * @param $id
     * @return bool
     */
    public static function validHash($hash, $id): bool
    {
        return $hash == static::getHash($id);
    }

    /**
     * @param $id
     * @return string
     */
    public static function getHash($id): string
    {
        $sha1 = sha1($id.static::HASH_PREFIX);
        if (strlen($sha1) > 10) {
            $sha1 = substr($sha1, 0, 10);
        }
        return $sha1;
    }

    /**
     * @param $value
     * @param int|null $length
     * @param bool $hashTag
     * @return string
     */
    public static function getModelIdToString($value, ?int $length = null, bool $hashTag = !1): string
    {
        $value = $value ?: '';
        $length ??= static::HASH_DEFAULT_ID_LENGTH;
        $id = str_pad($value, $length, '0', STR_PAD_LEFT);
        return ($hashTag ? '#' : '').$id;
    }

    /**
     * @param string $str
     * @param string $attributes
     * @param bool $prepend
     * @return string
     */
    public function __prepend(string $str = '+966', string $attributes = 'mobile', bool $prepend = !0): string
    {
        if (!($value = $this->{$attributes})) {
            return '';
        }
        return $prepend ? "$str$value" : "$value$str";
    }

    /**
     * Make attributes hidden fro array
     * @return string[]
     */
    public function defaultHiddenAttributes(): array
    {
        return ['deleted_at', 'updated_at', 'created_at', 'media'];
    }

    /**
     *
     * $this->name
     * @param $value
     *
     * @return string|null
     */
    public function getNameAttribute($value): ?string
    {
        $string = "";
        if ($value) {
            $string = $value;
        }
        else {
            $attr = locale_attribute();
            if ($this->isFillable($attr)) {
                $string = $this->{$attr};
            }
            elseif (method_exists($this, 'getNameColumn') && $this->getNameColumn() != 'name') {
                $string = $this->{$this->getNameColumn()};
            }
        }

        return (string) $string;
    }

    /**
     * Name of attribute will display tne model name Like created_at
     *
     * @return string
     */
    public function getNameColumn(): string
    {
        $class = class_basename(static::class);
        $class = Str::snake($class);
        $class = Str::singular($class);
        $class = strtolower($class);
        $array = [
            'name',
            locale_attribute(),
            "{$class}_name",
        ];
        $name = 'name';
        foreach ($array as $item) {
            if ($this->isFillable($item)) {
                $name = $item;
                break;
            }
        }
        return $name;
    }

    /**
     * @param $key
     *
     * @return mixed|string|null|array|void
     */
    public function __get($key)
    {
        if (! $key) {
            return;
        }

        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value. Otherwise, we will proceed as if the developers
        // are asking for a relationship's value. This covers both types of values.
        if (array_key_exists($key, $this->attributes) ||
            array_key_exists($key, $this->casts) ||
            $this->hasGetMutator($key) ||
            $this->hasAttributeMutator($key) ||
            $this->isClassCastable($key)) {
            return $this->getAttributeValue($key);
        }

        if (Helpers::hasTrait($this, HasTranslatorTrait::class)) {
            $locales = Translator::availableLocales();
            $attrs = $this->translatorAttributes();
            foreach ($attrs as $attr) {
                foreach ($locales as $locale) {
                    $ends = "_$key";
                    if (Str::endsWith($key, $ends) && ($attribute = Str::beforeLast($key, $ends)) && $attribute == $attr) {
                        return $this->translateAttribute($attribute, $locale);
                    }
                }
            }
        }

        /** get_{ATTRIBUTE}_from_{RELATION}_class */
        if (substr($key, 0, strlen(($get = "get_"))) == $get && substr($key, -strlen(($trait = "_class"))) == $trait) {
            $call = substr($key, strlen($get), (strlen($key) - strlen($get)) - strlen($trait));
            $callArray = explode("_from_", $call);
            krsort($callArray);
            $method = $this;
            $i = 0;
            foreach ($callArray as $item) {
                $i++;
                try {
                    $method = $method->{$item};
                }
                catch (Exception $exception) {
                    $method = '';
                }

                if ($i == count($callArray)) {
                    return ($method instanceof $this ? "" : (is_null($method) ? "" : $method));
                }
            }
        }

        /** get_{RELATION}_name */
        if (Str::startsWith($key, ($f = "get_")) && Str::endsWith($key, ($l = "_name"))) {
            $method = Str::before($key, $l);
            $method = Str::after($method, $f);
            if (($method && ($a = $this->{$method})) && method_exists($a, 'getNameColumn')) {
                return $a->{$a->getNameColumn()};
            }

            return '';
        }

        /** {ATTRIBUTE}_code */
        if (Str::endsWith($key, ($t = "_code")) && !$this->isFillable($key)) {
            if (($method = $this->__getMethod(Str::before($key, $t))) && !is_null(($a = $this->{$method}))) {
                return $a->code;
            }
            return null;
        }

        /** {ATTRIBUTE}_id_to_string */
        if (Str::endsWith($key, ($t = "_id_to_string"))) {
            $method = Str::before($key, $t);

            if (!is_null(($a = $this->{$method})) && method_exists($a, 'getNameColumn')) {
                return $a->{$a->getNameColumn()};
            }

            if (!is_null(($a = $this->{Str::camel($method)})) && method_exists($a, 'getNameColumn')) {
                return $a->{$a->getNameColumn()};
            }

            return !is_null(($a = $this->{$method})) && method_exists($a, 'getNameColumn') ? $a->{$a->getNameColumn()} : $a;
        }

        /** {ATTRIBUTE}_to_number_format */
        if (Str::endsWith($key, ($t = "_to_number_format"))) {
            $value = Str::before($key, $t);
            $number = $this->{$value};
            return to_number_format((float) ($number ?: 0), $this->numberFormat);
            // if ($number || $number == 0) {
            //$currency = config('4myth-tools.currency');
            //$balance = config('4myth-tools.currency_balance');
            /*try {
                if (($c = request()->header('app-currency'))) {
                    $currency = $c;
                }
            } catch (\Exception$exception) {
                $currency = '';
            }
            try {
                if (($c = request()->header('app-currency-balance'))) {
                    $balance = (float) $c;
                }
            } catch (\Exception$exception) {
                $balance = 1;
            }*/
            //$number *= $balance;
            //return to_number_format((float) $number, 2, $currency);
            // return to_number_format((float) $number);
            // }
            // return $number;
        }

        /** {ATTRIBUTE}_to_en_yes */
        if (Str::endsWith($key, ($t = "_to_en_yes")) && !$this->isFillable($key)) {
            $method = Str::before($key, $t);
            return !is_null(($_name = $this->{$method})) ? ($_name ? "yes" : "no") : $_name;
        }

        /** {ATTRIBUTE}_to_yes */
        if (Str::endsWith($key, ($t = "_to_yes")) && !$this->isFillable($key)) {
            $method = Str::before($key, $t);
            return !is_null(($_name = $this->{$method})) ? __("labels.".($_name ? "yes" : "no")) : $_name;
        }

        /** {$_ATTRIBUTE}_to_{$_format}_format */
        $datesFormats = config('4myth-tools.date_format');
        $snakeKey = Str::snake($key);
        foreach ($datesFormats as $format => $dateFormat) {
            if (Str::endsWith($snakeKey, ($t = "_to_{$format}_format"))) {
                $attribute = Str::before($snakeKey, $t);
                if (($date = $this->{$attribute})) {
                    !$date instanceof Carbon && ($date = Carbon::parse($date));
                    return date_by_locale($date->format($dateFormat));
                }
            }
        }

        /** {DATE_ATTRIBUTE}_to_hijri */
        if (Str::endsWith($snakeKey, ($t = "_to_hijri")) && ($attribute = Str::before($key, $t))) {
            if (($date = $this->{$attribute})) {
                !$date instanceof Carbon && ($date = Carbon::parse($date));
                return hijri($date);
            }
        }

        /** {DATE_ATTRIBUTE}_to_full_arabic_date */
        if (Str::endsWith($key, ($t = "_to_full_arabic_date"))) {
            $attribute = substr($key, 0, strlen($key) - strlen($t));
            if (($date = $this->{$attribute})) {
                // dd($attribute,$date,hijri($date)->format( app_date_format('date') ) );

                return arabic_date(hijri($date)->format(config('4myth-tools.date_format.hijri_human')));
            }
        }

        /** {DATE_ATTRIBUTE}_to_arabic_date */
        if (Str::endsWith($key, ($t = "_to_arabic_date"))) {
            $attribute = substr($key, 0, strlen($key) - strlen($t));
            if (($date = $this->{$attribute})) {
                !$date instanceof Carbon && ($date = Carbon::parse($date));
                return arabic_date(hijri($date)->format(config('4myth-tools.date_format.date')));
            }
        }

        /** {RELATION}_to_ids */
        if (Str::endsWith($key, ($t = "_to_ids"))) {
            $relation = Str::beforeLast($key, $t);
            if (method_exists($this, $relation)) {
                $m = $this->{$relation}();
                if ($m instanceof HasMany) {
                    return $m->pluck('id')->toArray();
                }
                if ($m instanceof BelongsToMany) {
                    $name = Str::snake(Str::singular($relation));
                    return $m->pluck("{$name}_id")->toArray();
                }
            }
        }

        /** Original */
        return parent::__get($key);
    }

    /**
     * @return string
     */
    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    /**
     * @return string
     */
    public function __idToString(): string
    {
        return static::getModelIdToString(value : $this->id);
    }

    /**
     * $this->created_at_to_string
     *
     * @return string|null
     */
    public function getCreatedAtToStringAttribute(): ?string
    {
        return $this->created_at ? $this->created_at->format(config('4myth-tools.date_format.date')) : null;
    }

    /**
     * $this->updated_at_to_string
     *
     * @return string|null
     */
    public function getUpdatedAtToStringAttribute(): ?string
    {
        return $this->updated_at ? $this->updated_at->format(config('4myth-tools.date_format.date')) : null;
    }

    /**
     * Check if model has method
     *
     * @param $method
     *
     * @return string|null
     */
    public function __getMethod($method): ?string
    {
        $methods = Helpers::strCasesArray($method);

        foreach ($methods as $m) {
            if (method_exists($this, $m)) {
                return $m;
            }
        }
        return null;
    }

    /**
     * Get the model's preferred locale.
     */
    public function preferredLocale(): string
    {
        return $this->locale ?? config('app.locale');
    }

    /**
     * @param array<string,int> $except - e.g. [ 'user_id' => 1 ]
     * @return static
     */
    public function cloneModel(array $except = []): static
    {
        $clone = $this->replicate(array_keys($except));
        if (empty($except)) {
            if ($clone->isFillable('name')) {
                $clone->name = __('replace.copy_of', ['name' => $this->name]);
            }
            if ($clone->isFillable('name_ar')) {
                $clone->name_ar = __('replace.copy_of', ['name' => $this->name_ar], 'ar');
            }
            if ($clone->isFillable('name_en')) {
                $clone->name_en = __('replace.copy_of', ['name' => $this->name_en], 'en');
            }
            if ($clone->isFillable($s = Str::snake(class_basename($this)).'_name')) {
                $clone->{$s} = __('replace.copy_of', ['name' => $clone->{$s}]);
            }
            if ($clone->isFillable($s = Str::snake(class_basename($this)).'_name_ar')) {
                $clone->{$s} = __('replace.copy_of', ['name' => $clone->{$s}]);
            }
            if ($clone->isFillable($s = Str::snake(class_basename($this)).'_name_en')) {
                $clone->{$s} = __('replace.copy_of', ['name' => $clone->{$s}]);
            }
            if ($clone->isFillable('order_by')) {
                $clone->order_by = $this->order_by + 1;
            }
        }
        foreach ($except as $k => $v) {
            if ($clone->isFillable($k)) {
                $clone->{$k} = $v;
            }
        }
        if ($this->created_at) {
            $clone->created_at = now();
        }
        if ($this->updated_at) {
            $clone->updated_at = now();
        }
        if ($this->deleted_at) {
            $clone->deleted_at = null;
        }
        try {
            $clone->fireModelEvent('cloning', !1);
            $clone->push();
            $clone->fireModelEvent('cloned', !1);
        }
        catch (\Exception $e) {
        }
        try {
            if (method_exists($this, 'media')) {
                $media = $this->media()->get();
                /** @var Media $file */
                foreach ($media as $file) {
                    $file->copy($clone, $file->collection_name);
                }
            }
        }
        catch (Exception $e) {
            //
        }

        foreach ($this->cloneRelations as $relationName) {
            /** @var self $relation */
            foreach ($this->{$relationName} as $relation) {
                $relation->cloneModel([$clone->getForeignKey() => $clone->getKey()]);
            }
        }
        return $clone;
    }

    /**
     * @param Builder $builder
     * @param $value
     *
     * @return Builder
     */
    protected function scopeFromCreatedAt(Builder $builder, $value): Builder
    {
        return $builder->whereDate('created_at', '>=', $value);
    }

    /**
     * @param Builder $builder
     * @param $value
     *
     * @return Builder
     */
    protected function scopeToCreatedAt(Builder $builder, $value): Builder
    {
        return $builder->whereDate('created_at', '<=', $value);
    }
}

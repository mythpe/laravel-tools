<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Models;

use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Myth\LaravelTools\Traits\BaseModel\HasMediaTrait;
use Myth\LaravelTools\Traits\BaseModel\SlugModelTrait;
use Spatie\MediaLibrary\HasMedia;

class BaseModel extends Authenticatable implements HasMedia
{
    use HasFactory;
    use Notifiable;
    use HasMediaTrait;
    use SlugModelTrait;

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $name = locale_attribute();
        if ($this->isFillable($name) && !$this->isFillable('name')) {
            $this->append(['name']);
        }
        //$this->append('created_at_to_string', 'updated_at_to_string');
        $this->makeHidden('deleted_at', 'updated_at', 'created_at', 'media');
    }

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
     * @param $value
     *
     * @return string
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
            elseif ($this->getNameColumn() != 'name') {
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
        $fill = [
            'name',
            locale_attribute(),
            "{$class}_name",
        ];
        $name = 'name';
        foreach ($fill as $item) {
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
        if (!$key) {
            return;
        }

        // If the attribute exists in the attribute array or has a "get" mutator we will
        // get the attribute's value. Otherwise, we will proceed as if the developers
        // are asking for a relationship's value. This covers both types of values.
        if (array_key_exists($key, $this->attributes) || array_key_exists($key, $this->casts) || $this->hasGetMutator($key) || $this->isClassCastable($key)) {
            return $this->getAttributeValue($key);
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
            return (($method && ($a = $this->{$method})) ? $a->{$a->getNameColumn()} : '');
        }

        /** {ATTRIBUTE}_code */
        if (Str::endsWith($key, ($t = "_code")) && !$this->isFillable($key)) {
            $method = $this->modelHasMethod(Str::before($key, $t));

            if (!is_null(($a = $this->{$method}))) {
                return $a->code;
            }
        }

        /** {ATTRIBUTE}_id_to_string */
        if (Str::endsWith($key, ($t = "_id_to_string"))) {
            $method = Str::before($key, $t);

            if (!is_null(($a = $this->{$method}))) {
                return $a->{$a->getNameColumn()};
            }

            if (!is_null(($a = $this->{Str::camel($method)}))) {
                return $a->{$a->getNameColumn()};
            }

            return !is_null(($a = $this->{$method})) ? $a->{$a->getNameColumn()} : $a;
        }

        /** {ATTRIBUTE}_to_number_format */
        if (Str::endsWith($key, ($t = "_to_number_format"))) {
            $value = Str::before($key, $t);
            $number = $this->{$value};
            if ($number || $number == 0) {
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
                        $balance = floatval($c);
                    }
                } catch (\Exception$exception) {
                    $balance = 1;
                }*/
                //$number *= $balance;
                //return to_number_format((float) $number, 2, $currency);
                return to_number_format((float) $number);
            }

            return $number;
        }

        /** {ATTRIBUTE}_to_en_yes */
        if (Str::endsWith($key, ($t = "_to_en_yes")) && !$this->isFillable($key)) {
            $method = Str::before($key, $t);
            return !is_null(($_name = $this->{$method})) ? ($_name ? "yes" : "no") : $_name;
        }

        /** {ATTRIBUTE}_to_yes */
        if (Str::endsWith($key, ($t = "_to_yes")) && !$this->isFillable($key)) {
            $method = Str::before($key, $t);
            return !is_null(($_name = $this->{$method})) ? __("global.".($_name ? "yes" : "no")) : $_name;
        }

        /** {DATE_ATTRIBUTE}_to_date_format */
        if (Str::endsWith($key, ($t = "_to_date_format")) && ($attribute = Str::before($key, $t))) {
            if ($this->isDateAttribute($attribute) && ($date = $this->{$attribute})) {
                !$date instanceof Carbon && ($date = Carbon::parse($date));
                return $date->format(config('4myth-tools.date_format.date'));
            }
        }

        /** {DATE_ATTRIBUTE}_to_time_format */
        if (Str::endsWith($key, ($t = "_to_time_format")) && ($attribute = Str::before($key, $t))) {
            if ($this->isDateAttribute($attribute) && ($date = $this->{$attribute})) {
                !$date instanceof Carbon && ($date = Carbon::parse($date));
                return $date->format(config('4myth-tools.date_format.time'));
            }
        }

        /** {DATE_ATTRIBUTE}_to_time_string_format */
        if (Str::endsWith($key, ($t = "_to_time_string_format")) && ($attribute = Str::before($key, $t))) {
            if ($this->isDateAttribute($attribute) && ($date = $this->{$attribute})) {
                !$date instanceof Carbon && ($date = Carbon::parse($date));
                return date_by_locale($date->format(config('4myth-tools.date_format.time_string')));
            }
        }

        /** {DATE_ATTRIBUTE}_to_datetime_format */
        if (Str::endsWith($key, ($t = "_to_datetime_format")) && ($attribute = Str::before($key, $t))) {
            if ($this->isDateAttribute($attribute) && ($date = $this->{$attribute})) {
                !$date instanceof Carbon && ($date = Carbon::parse($date));
                return date_by_locale($date->format(config('4myth-tools.date_format.datetime')));
            }
        }

        /** {DATE_ATTRIBUTE}_to_fulldatetime_format */
        if (Str::endsWith($key, ($t = "_to_fulldatetime_format")) && ($attribute = Str::before($key, $t))) {
            if ($this->isDateAttribute($attribute) && ($date = $this->{$attribute})) {
                !$date instanceof Carbon && ($date = Carbon::parse($date));
                return date_by_locale($date->format(config('4myth-tools.date_format.full')));
            }
        }

        /** {DATE_ATTRIBUTE}_to_day_format */
        if (Str::endsWith($key, ($t = "_to_day_format"))) {
            $attribute = substr($key, 0, strlen($key) - strlen($t));
            if ($this->isDateAttribute($attribute) && ($date = $this->{$attribute})) {
                !$date instanceof Carbon && ($date = Carbon::parse($date));
                return date_by_locale($date->format(config('4myth-tools.date_format.day')));
            }
        }

        /** {DATE_ATTRIBUTE}_to_hijri */
        if (Str::endsWith($key, ($t = "_to_hijri"))) {
            $attribute = substr($key, 0, strlen($key) - strlen($t));
            if ($this->isDateAttribute($attribute) && ($date = $this->{$attribute})) {
                !$date instanceof Carbon && ($date = Carbon::parse($date));
                return hijri($date);
            }
        }

        /** {DATE_ATTRIBUTE}_to_full_arabic_date */
        if (Str::endsWith($key, ($t = "_to_full_arabic_date"))) {
            $attribute = substr($key, 0, strlen($key) - strlen($t));
            if ($this->isDateAttribute($attribute) && ($date = $this->{$attribute})) {
                // dd($attribute,$date,hijri($date)->format( app_date_format('date') ) );

                return arabic_date(hijri($date)->format(config('4myth-tools.date_format.hijri_human')));
            }
        }

        /** {DATE_ATTRIBUTE}_to_arabic_date */
        if (Str::endsWith($key, ($t = "_to_arabic_date"))) {
            $attribute = substr($key, 0, strlen($key) - strlen($t));
            if ($this->isDateAttribute($attribute) && ($date = $this->{$attribute})) {
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
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFromCreatedAt(Builder $builder, $value): Builder
    {
        return $builder->whereDate('created_at', '>=', $value);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeToCreatedAt(Builder $builder, $value): Builder
    {
        return $builder->whereDate('created_at', '<=', $value);
    }

    /**
     * @param  string  $code
     *
     * @return string
     */
    public function getMobileWithCountryCode(string $code = '+966'): string
    {
        if (($mobile = $this->mobile)) {
            return "{$code}{$mobile}";
        }
        return '';
    }

    /**
     * @param  int  $length
     * @param  bool  $hashTag
     * @param  null  $value
     *
     * @return string
     */
    public function getIdString(int $length = 4, bool $hashTag = !0, $value = null): string
    {
        $value = is_null($value) ? $this->getKey() : $value;
        $id = str_pad($value, $length, '0', STR_PAD_LEFT);
        return ($hashTag ? '#' : '').$id;
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
     * @return array
     */
    public function getAppends(): array
    {
        return $this->appends;
    }

    /**
     * Check if model has method
     *
     * @param $method
     *
     * @return string|null
     */
    protected function modelHasMethod($method): ?string
    {
        $methods = $this->strCasesArray($method);

        foreach ($methods as $m) {
            if (method_exists($this, $m)) {
                return $m;
            }
        }
        return null;
    }

    /**
     * helper
     *
     * @param $str
     *
     * @return array
     */
    protected function strCasesArray($str): array
    {
        return collect([
            $str,
            Str::snake($str),
            Str::camel($str),
        ])->uniqueStrict()->toArray();
    }
}

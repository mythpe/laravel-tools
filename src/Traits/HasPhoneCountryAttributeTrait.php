<?php
/*
 * MyTh Ahmed Faiz Copyright © 2025 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Propaganistas\LaravelPhone\PhoneNumber;

/**
 * @property PhoneNumber $phone
 * @property string $phone_country
 * @property string $phone_to_string as phone phone_normalized
 * @property string $phone_normalized country code without [+].
 * @property string $phone_international as a database save with [+].
 * @property string $phone_national Normal phone. Local.
 */
trait HasPhoneCountryAttributeTrait
{
    /**
     * @return string
     */
    public function phoneCountryAttributeKey(): string
    {
        return 'phone_country';
    }

    /**
     * @return string
     */
    public function phoneAttributeKey(): string
    {
        return 'phone';
    }

    /**
     * @return Attribute
     */
    protected function phoneCountry(): Attribute
    {
        return Attribute::make(
            get : fn($v) => $v,
            set : fn($value) => ($this->attributes[$this->phoneCountryAttributeKey()] = $value && is_string($value) ? strtoupper($value) : $value),
        );
    }

    /**
     * $this->phone_to_string
     * as phone national
     *
     * @return Attribute
     */
    protected function phoneToString(): Attribute
    {
        return Attribute::get(
            // fn() => $this->{$this->phoneAttributeKey()}?->formatForMobileDialingInCountry($this->{$this->phoneCountryAttributeKey()})
            fn() => $this->phone_normalized
        );
    }

    /**
     * $this->phone_national
     * Normal phone.
     *
     * @return Attribute
     */
    protected function phoneNational(): Attribute
    {
        return Attribute::get(
            fn() => $this->{$this->phoneAttributeKey()}?->formatForMobileDialingInCountry($this->{$this->phoneCountryAttributeKey()})
        );
    }

    /**
     * $this->phone_international
     *  with country code & [+]
     *
     * @return Attribute
     */
    protected function phoneInternational(): Attribute
    {
        return Attribute::get(fn() => preg_replace('/\s+/', '', $this->{$this->phoneAttributeKey()}?->formatInternational() ?? ''));
    }

    /**
     * $this->phone_normalized
     * with country code without [+]
     *
     * @return Attribute
     */
    protected function phoneNormalized(): Attribute
    {
        return Attribute::get(fn() => preg_replace('/\D/', '', $this->{$this->phoneAttributeKey()}?->formatE164() ?? ''));
    }
}

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
use Myth\LaravelTools\Utilities\Helpers;

/**
 * @property-read string|null $country
 * @property-read string|null $country_to_string
 */
trait HasCountryAttributeTrait
{
    /**
     * $this->country_to_string
     * @return Attribute
     */
    protected function countryToString(): Attribute
    {
        return Attribute::get(fn() => Helpers::getCountryDisplayName($this->country));
    }
}

<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\Utilities;

/**
 * @property-read string|mixed $description
 */
trait HasDescriptionAttribute
{
    /**
     * $this->description
     *
     * @param $value
     *
     * @return string|mixed
     */
    public function getDescriptionAttribute($value): mixed
    {
        if ($value) {
            return $value;
        }
        return (string) $this->{locale_attribute('description')};
    }
}

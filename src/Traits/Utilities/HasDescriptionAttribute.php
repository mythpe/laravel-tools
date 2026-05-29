<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * @property-read string|mixed $description
 */
trait HasDescriptionAttribute
{
    /**
     * $this->description
     *
     * @return Attribute
     */
    protected function description(): Attribute
    {
        return Attribute::get(fn() => $this->{locale_attribute('description')});
    }
}

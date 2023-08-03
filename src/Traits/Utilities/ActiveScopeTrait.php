<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Illuminate\Database\Eloquent\Builder;

/**
 * @property bool $active
 * @property-read string $active_to_string
 */
trait ActiveScopeTrait
{
    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeActiveOnly(Builder $builder): Builder
    {
        return $builder->where('active', !0);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeInactiveOnly(Builder $builder): Builder
    {
        return $builder->where('active', !1);
    }

    /**
     * $this->active_to_string
     *
     * @return string
     */
    public function getActiveToStringAttribute(): string
    {
        if ($this->active) {
            return __("static.statuses.active");
        }
        return __("static.statuses.inactive");
    }
}

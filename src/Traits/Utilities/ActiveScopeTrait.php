<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
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
 * @method static Builder<static>|static activeOnly()
 * @method static Builder<static>|static inactiveOnly()
 */
trait ActiveScopeTrait
{
    /**
     * @param Builder<static> $builder
     *
     * @return Builder<static>
     */
    public function scopeActiveOnly(Builder $builder): Builder
    {
        return $builder->where('active', !0);
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $save
     * @return $this
     */
    public function setActive(bool $save = !0): static
    {
        if ($this->exists) {
            $this->active = !0;
            $save && $this->save();
        }
        return $this;
    }

    /**
     * @param Builder<static> $builder
     *
     * @return Builder<static>
     */
    public function scopeInactiveOnly(Builder $builder): Builder
    {
        return $builder->where('active', !1);
    }

    /**
     * @return bool
     */
    public function isInactive(): bool
    {
        return !$this->active;
    }

    /**
     * @param bool $save
     * @return $this
     */
    public function setInactive(bool $save = !0): static
    {
        if ($this->exists) {
            $this->active = !1;
            $save && $this->save();
        }
        return $this;
    }

    /**
     * $this->active_to_string
     *
     * @return string
     */
    public function getActiveToStringAttribute(): string
    {
        if ($this->active) {
            return __("const.statuses.active");
        }
        return __("const.statuses.inactive");
    }
}

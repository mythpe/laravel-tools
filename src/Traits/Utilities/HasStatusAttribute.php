<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * @property string $status
 * @property-read string $status_to_string
 */
trait HasStatusAttribute
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public static function getStatuses(): Collection
    {
        $keys = array_values(static::getStatusesCodes());
        $lang = collect(__("static.statuses"))->only($keys);
        return $lang->map(fn($text, $id) => [
            'id'    => $id,
            'value' => $id,
            'key'   => $id,
            'text'  => $text,
            'name'  => $text,
        ])->sortBy('name')->values();
    }

    /**
     * @return string[]
     */
    public static function getStatusesCodes(): array
    {
        return [
            "active",
            "inactive",
            "pending",
            "approved",
            "canceled",
            "used",
            'banded',
            'unpaid',
            'paid',
            'finished',
            'activated',
            'confirmed',
            'unconfirmed',
            'new',
            'archived',
            'completed',
            'rejected',
        ];
    }

    /**
     * @param $status
     *
     * @return string|null
     */
    public static function getStatus($status): ?string
    {
        return static::getStatusesCodes()[$status] ?? null;
    }

    /**
     * $this->status_to_string
     * $this->statusToString
     *
     * @return string
     */
    public function getStatusToStringAttribute(): string
    {
        return trans_has(($k = "static.statuses.$this->status")) ? __($k) : "";
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApprovedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::APPROVED_STATUS);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnpaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::UNPAID_STATUS);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::PAID_STATUS);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCanceledOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::CANCELED_STATUS);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinishedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::FINISHED_STATUS);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNewOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::NEW_STATUS);
    }

    /**
     * @param  bool  $save
     *
     * @return void
     */
    public function setApproved(bool $save = !0): void
    {
        $this->status = static::APPROVED_STATUS;
        $save && $this->save();
    }

    /**
     * @param  bool  $save
     *
     * @return void
     */
    public function setPending(bool $save = !0): void
    {
        $this->status = static::PENDING_STATUS;
        $save && $this->save();
    }

    /**
     * @param  bool  $save
     *
     * @return void
     */
    public function setPaid(bool $save = !0): void
    {
        $this->status = static::PAID_STATUS;
        $save && $this->save();
    }

    /**
     * @param  bool  $save
     *
     * @return void
     */
    public function setUnpaid(bool $save = !0): void
    {
        $this->status = static::UNPAID_STATUS;
        $save && $this->save();
    }

    /**
     * @param  bool  $save
     *
     * @return void
     */
    public function setCanceled(bool $save = !0): void
    {
        $this->status = static::CANCELED_STATUS;
        $save && $this->save();
    }

    /**
     * @param  bool  $save
     *
     * @return void
     */
    public function setConfirmed(bool $save = !0): void
    {
        $this->status = static::CONFIRMED_STATUS;
        $save && $this->save();
    }

    /**
     * @param  bool  $save
     *
     * @return void
     */
    public function setCompleted(bool $save = !0): void
    {
        $this->status = static::COMPLETED_STATUS;
        $save && $this->save();
    }

    /**
     * @param  bool  $save
     *
     * @return void
     */
    public function setRejected(bool $save = !0): void
    {
        $this->status = static::REJECTED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isApproved(): bool
    {
        return $this->status == static::APPROVED_STATUS;
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status == static::PENDING_STATUS;
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->status == static::FINISHED_STATUS;
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status == static::PAID_STATUS;
    }

    /**
     * @return bool
     */
    public function isUnpaid(): bool
    {
        return $this->status == static::UNPAID_STATUS;
    }

    /**
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status == static::CANCELED_STATUS;
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->status == static::NEW_STATUS;
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->status == static::CONFIRMED_STATUS;
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status == static::COMPLETED_STATUS;
    }

    /**
     * @return bool
     */
    public function isRejected(): bool
    {
        return $this->status == static::REJECTED_STATUS;
    }
}

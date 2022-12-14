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
        $lang = collect(__("static.statuses") ?: [])->only(static::getStatusesCodes());
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
            'activated',
            'active',
            'approved',
            'archived',
            'banded',
            'canceled',
            'completed',
            'confirmed',
            'finished',
            'inactive',
            'new',
            'paid',
            'pending',
            'rejected',
            'unconfirmed',
            'unpaid',
            'used',
        ];
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
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActivatedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::ACTIVATED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotActivatedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::ACTIVATED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setActivated(bool $save = !0): void
    {
        $this->status = static::ACTIVATED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isActivated(): bool
    {
        return $this->status == static::ACTIVATED_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActiveOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::ACTIVE_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotActiveOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::ACTIVE_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setActive(bool $save = !0): void
    {
        $this->status = static::ACTIVE_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status == static::ACTIVE_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeApprovedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::APPROVED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotApprovedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::APPROVED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setApproved(bool $save = !0): void
    {
        $this->status = static::APPROVED_STATUS;
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
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchivedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::ARCHIVED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotArchivedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::ARCHIVED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setArchived(bool $save = !0): void
    {
        $this->status = static::ARCHIVED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isArchived(): bool
    {
        return $this->status == static::ARCHIVED_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeBandedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::BANDED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotBandedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::BANDED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setBanded(bool $save = !0): void
    {
        $this->status = static::BANDED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isBanded(): bool
    {
        return $this->status == static::BANDED_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCanceledOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::CANCELED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotCanceledOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::CANCELED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setCanceled(bool $save = !0): void
    {
        $this->status = static::CANCELED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isCanceled(): bool
    {
        return $this->status == static::CANCELED_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompletedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::COMPLETED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotCompletedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::COMPLETED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setCompleted(bool $save = !0): void
    {
        $this->status = static::COMPLETED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isCompleted(): bool
    {
        return $this->status == static::COMPLETED_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeConfirmedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::CONFIRMED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotConfirmedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::CONFIRMED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setConfirmed(bool $save = !0): void
    {
        $this->status = static::CONFIRMED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isConfirmed(): bool
    {
        return $this->status == static::CONFIRMED_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFinishedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::FINISHED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotFinishedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::FINISHED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setFinished(bool $save = !0): void
    {
        $this->status = static::FINISHED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isFinished(): bool
    {
        return $this->status == static::FINISHED_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactiveOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::INACTIVE_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotInactiveOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::INACTIVE_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setInactive(bool $save = !0): void
    {
        $this->status = static::INACTIVE_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isInactive(): bool
    {
        return $this->status == static::INACTIVE_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNewOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::NEW_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotNewOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::NEW_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setNew(bool $save = !0): void
    {
        $this->status = static::NEW_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->status == static::NEW_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::PAID_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotPaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::PAID_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setPaid(bool $save = !0): void
    {
        $this->status = static::PAID_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->status == static::PAID_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendingOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::PENDING_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotPendingOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::PENDING_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setPending(bool $save = !0): void
    {
        $this->status = static::PENDING_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isPending(): bool
    {
        return $this->status == static::PENDING_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRejectedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::REJECTED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotRejectedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::REJECTED_STATUS);
    }

    /**
     * @param bool $save
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
    public function isRejected(): bool
    {
        return $this->status == static::REJECTED_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnconfirmedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::UNCONFIRMED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotUnconfirmedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::UNCONFIRMED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setUnconfirmed(bool $save = !0): void
    {
        $this->status = static::UNCONFIRMED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isUnconfirmed(): bool
    {
        return $this->status == static::UNCONFIRMED_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnpaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::UNPAID_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotUnpaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::UNPAID_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setUnpaid(bool $save = !0): void
    {
        $this->status = static::UNPAID_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isUnpaid(): bool
    {
        return $this->status == static::UNPAID_STATUS;
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUsedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::USED_STATUS);
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeNotUsedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::USED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setUsed(bool $save = !0): void
    {
        $this->status = static::USED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isUsed(): bool
    {
        return $this->status == static::USED_STATUS;
    }
}

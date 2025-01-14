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
use Illuminate\Support\Collection;

/**
 * @property string $status
 * @property-read string $status_to_string
 */
trait HasStatusAttribute
{
    /** @var string */
    const ACTIVATED_STATUS = 'activated';

    /** @var string */
    const ACTIVE_STATUS = 'active';

    /** @var string */
    const APPROVED_STATUS = 'approved';

    /** @var string */
    const ARCHIVED_STATUS = 'archived';

    /** @var string */
    const BANDED_STATUS = 'banded';

    /** @var string */
    const CANCELED_STATUS = 'canceled';

    /** @var string */
    const COMPLETED_STATUS = 'completed';

    /** @var string */
    const CONFIRMED_STATUS = 'confirmed';

    /** @var string */
    const DELETED_STATUS = 'deleted';

    /** @var string */
    const DELIVERED_STATUS = 'delivered';

    /** @var string */
    const DISABLED_STATUS = 'disabled';

    /** @var string */
    const DRAFT_STATUS = 'draft';

    /** @var string */
    const FINISHED_STATUS = 'finished';

    /** @var string */
    const INACTIVE_STATUS = 'inactive';

    /** @var string */
    const NEW_STATUS = 'new';

    /** @var string */
    const PAID_STATUS = 'paid';

    /** @var string */
    const PARTIAL_PAID_STATUS = 'partial_paid';

    /** @var string */
    const PARTIAL_RETURNED_STATUS = 'partial_returned';

    /** @var string */
    const PENDING_STATUS = 'pending';

    /** @var string */
    const PENDING_PAYMENT_STATUS = 'pending_payment';

    /** @var string */
    const PROCESSING_STATUS = 'processing';

    /** @var string */
    const REJECTED_STATUS = 'rejected';

    /** @var string */
    const RETURNED_STATUS = 'returned';

    /** @var string */
    const SHIPPED_STATUS = 'shipped';

    /** @var string */
    const UNCONFIRMED_STATUS = 'unconfirmed';

    /** @var string */
    const UNPAID_STATUS = 'unpaid';

    /** @var string */
    const USED_STATUS = 'used';

    /**
     * @return Collection
     */
    public static function getStatuses(): Collection
    {
        $lang = collect(__("const.statuses") ?: [])->only(static::getStatusesCodes());
        return $lang->map(fn($text, $id) => [
            'id'    => $id,
            'value' => $id,
            'label' => $text,
        ])->values();
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
            'delivered',
            'draft',
            'finished',
            'inactive',
            'new',
            'paid',
            'partial_paid',
            'partial_returned',
            'pending',
            'pending_payment',
            'processing',
            'rejected',
            'returned',
            'shipped',
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
        return trans_has(($k = "const.statuses.$this->status")) ? __($k) : "";
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeActivatedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::ACTIVATED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeActiveOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::ACTIVE_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeApprovedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::APPROVED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeArchivedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::ARCHIVED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeBandedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::BANDED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeCanceledOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::CANCELED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeCompletedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::COMPLETED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeConfirmedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::CONFIRMED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeDeletedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::DELETED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotDeletedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::DELETED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setDeleted(bool $save = !0): void
    {
        $this->status = static::DELETED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isDeleted(): bool
    {
        return $this->status == static::DELETED_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeDeliveredOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::DELIVERED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotDeliveredOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::DELIVERED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setDelivered(bool $save = !0): void
    {
        $this->status = static::DELIVERED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isDelivered(): bool
    {
        return $this->status == static::DELIVERED_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeDisabledOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::DISABLED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotDisabledOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::DISABLED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setDisabled(bool $save = !0): void
    {
        $this->status = static::DISABLED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isDisabled(): bool
    {
        return $this->status == static::DISABLED_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeDraftOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::DRAFT_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotDraftOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::DRAFT_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setDraft(bool $save = !0): void
    {
        $this->status = static::DRAFT_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isDraft(): bool
    {
        return $this->status == static::DRAFT_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeFinishedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::FINISHED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeInactiveOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::INACTIVE_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNewOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::NEW_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopePaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::PAID_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopePartialPaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::PARTIAL_PAID_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotPartialPaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::PARTIAL_PAID_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setPartialPaid(bool $save = !0): void
    {
        $this->status = static::PARTIAL_PAID_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isPartialPaid(): bool
    {
        return $this->status == static::PARTIAL_PAID_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopePartialReturnedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::PARTIAL_RETURNED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotPartialReturnedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::PARTIAL_RETURNED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setPartialReturned(bool $save = !0): void
    {
        $this->status = static::PARTIAL_RETURNED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isPartialReturned(): bool
    {
        return $this->status == static::PARTIAL_RETURNED_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopePendingOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::PENDING_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopePendingPaymentOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::PENDING_PAYMENT_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotPendingPaymentOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::PENDING_PAYMENT_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setPendingPayment(bool $save = !0): void
    {
        $this->status = static::PENDING_PAYMENT_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isPendingPayment(): bool
    {
        return $this->status == static::PENDING_PAYMENT_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeProcessingOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::PROCESSING_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotProcessingOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::PROCESSING_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setProcessing(bool $save = !0): void
    {
        $this->status = static::PROCESSING_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isProcessing(): bool
    {
        return $this->status == static::PROCESSING_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeRejectedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::REJECTED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeReturnedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::RETURNED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotReturnedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::RETURNED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setReturned(bool $save = !0): void
    {
        $this->status = static::RETURNED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isReturned(): bool
    {
        return $this->status == static::RETURNED_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeShippedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::SHIPPED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeNotShippedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '!=', static::SHIPPED_STATUS);
    }

    /**
     * @param bool $save
     *
     * @return void
     */
    public function setShipped(bool $save = !0): void
    {
        $this->status = static::SHIPPED_STATUS;
        $save && $this->save();
    }

    /**
     * @return bool
     */
    public function isShipped(): bool
    {
        return $this->status == static::SHIPPED_STATUS;
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeUnconfirmedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::UNCONFIRMED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeUnpaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::UNPAID_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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
     * @param Builder $builder
     *
     * @return Builder
     */
    public function scopeUsedOnly(Builder $builder): Builder
    {
        return $builder->where('status', static::USED_STATUS);
    }

    /**
     * @param Builder $builder
     *
     * @return Builder
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

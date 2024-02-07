<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\PaymentGetaway;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder purchaseOnly()
 * @method static Builder refundOnly()
 * @method static Builder voidPurchaseOnly()
 * @method static Builder authorizationOnly()
 * @method static Builder captureOnly()
 * @method static Builder voidRefundOnly()
 * @method static Builder voidAuthorizationOnly()
 * @method static Builder transactionInquiryOnly()
 */
trait GetawayActionsTrait
{

    /**
     * @param string|null $key
     * @return array|string|null
     */
    public static function getTransactionActions(?string $key = null): array | string | null
    {
        $actions = config('4myth-getaway.actions', []);
        if ($key && !array_key_exists($key, $actions)) {
            throw new \InvalidArgumentException("Invalid key passed $key. Must One Of ".implode(',', array_keys($actions)));
        }
        return !is_null($key) ? ($actions[$key] ?? null) : $actions;
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopePurchaseOnly(Builder $builder): Builder
    {
        return $builder->where('action', '=', config('4myth-getaway.actions.purchase', 1));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeRefundOnly(Builder $builder): Builder
    {
        return $builder->where('action', '=', config('4myth-getaway.actions.refund', 2));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeVoidPurchaseOnly(Builder $builder): Builder
    {
        return $builder->where('action', '=', config('4myth-getaway.actions.void_purchase', 3));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeAuthorizationOnly(Builder $builder): Builder
    {
        return $builder->where('action', '=', config('4myth-getaway.actions.authorization', 4));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeCaptureOnly(Builder $builder): Builder
    {
        return $builder->where('action', '=', config('4myth-getaway.actions.capture', 5));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeVoidRefundOnly(Builder $builder): Builder
    {
        return $builder->where('action', '=', config('4myth-getaway.actions.void_refund', 6));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeVoidAuthorizationOnly(Builder $builder): Builder
    {
        return $builder->where('action', '=', config('4myth-getaway.actions.void_authorization', 9));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeTransactionInquiryOnly(Builder $builder): Builder
    {
        return $builder->where('action', '=', config('4myth-getaway.actions.transaction_inquiry', 10));
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->response_code == '000';
    }

    /**
     * @return bool
     */
    public function isPurchase(): bool
    {
        return $this->action == config('4myth-getaway.actions.purchase');
    }

    /**
     * @return bool
     */
    public function isRefund(): bool
    {
        return $this->action == config('4myth-getaway.actions.refund');
    }

    /**
     * @return bool
     */
    public function isVoidPurchase(): bool
    {
        return $this->action == config('4myth-getaway.actions.void_purchase');
    }

    /**
     * @return bool
     */
    public function isAuthorization(): bool
    {
        return $this->action == config('4myth-getaway.actions.authorization');
    }

    /**
     * @return bool
     */
    public function isCapture(): bool
    {
        return $this->action == config('4myth-getaway.actions.capture');
    }

    /**
     * @return bool
     */
    public function isVoidRefund(): bool
    {
        return $this->action == config('4myth-getaway.actions.void_refund');
    }

    /**
     * @return bool
     */
    public function isVoidAuthorization(): bool
    {
        return $this->action == config('4myth-getaway.actions.void_authorization');
    }

    /**
     * @return bool
     */
    public function isTransactionInquiry(): bool
    {
        return $this->action == config('4myth-getaway.actions.transaction_inquiry');
    }

    /**
     * @return bool
     */
    public function canInquiry(): bool
    {
        return $this->isSuccess();
    }

    /**
     * @return bool
     */
    public function canVoidRefund(): bool
    {
        return !$this->isUsed() && $this->isSuccess() && $this->isRefund();
    }

    /**
     * @return bool
     */
    public function canRefund(): bool
    {
        if ($this->isUsed() || !$this->isSuccess()) {
            return !1;
        }
        if (!$this->isPurchase() && !$this->isAuthorization()) {
            return !1;
        }
        $hasCapture = $this->order->transactions()->successOnly()->captureOnly()->exists();
        $outstandingAmount = $this->order->getOutstandingAmount();
        if ($this->isAuthorization()) {
            return $hasCapture && $outstandingAmount > 0;
        }
        return $outstandingAmount > 0;
    }

    /**
     * @return bool
     */
    public function canVoidPurchase(): bool
    {
        return !$this->isUsed() && $this->isSuccess() && $this->isPurchase();
    }

    /**
     * @return bool
     */
    public function canVoidAuthorization(): bool
    {
        return !$this->isUsed() && $this->isSuccess() && $this->isAuthorization();
    }

    /**
     * @return bool
     */
    public function canCapture(): bool
    {
        if (!$this->isAuthorization() || !$this->isSuccess()) {
            return !1;
        }
        return !$this->isUsed() && $this->isAuthorization() && !$this->order->transactions()->successOnly()->captureOnly()->exists();
    }

    /**
     * @return string
     */
    public function getPurchaseAction(): string
    {
        return config('4myth-getaway.actions.purchase', 1);
    }

    /**
     * @return string
     */
    public function getRefundAction(): string
    {
        return config('4myth-getaway.actions.refund', 2);
    }

    /**
     * @return string
     */
    public function getVoidPurchaseAction(): string
    {
        return config('4myth-getaway.actions.void_purchase', 3);
    }

    /**
     * @return string
     */
    public function getAuthorizationAction(): string
    {
        return config('4myth-getaway.actions.authorization', 4);
    }

    /**
     * @return string
     */
    public function getCaptureAction(): string
    {
        return config('4myth-getaway.actions.capture', 5);
    }

    /**
     * @return string
     */
    public function getVoidRefundAction(): string
    {
        return config('4myth-getaway.actions.void_refund', 6);
    }

    /**
     * @return string
     */
    public function getVoidAuthorizationAction(): string
    {
        return config('4myth-getaway.actions.void_authorization', 9);
    }

    /**
     * @return string
     */
    public function getTransactionInquiryAction(): string
    {
        return config('4myth-getaway.actions.transaction_inquiry', 10);
    }

    /**
     * @return array
     */
    public function actionsCantDoTransaction(): array
    {
        return [static::getVoidAuthorizationAction(), static::getVoidPurchaseAction(), static::getVoidRefundAction()];
    }
}
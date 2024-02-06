<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\PaymentGetaway;

/**
 *
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
    public function canVoidRefund(): bool
    {
        return $this->isSuccess() && $this->isRefund();
    }

    /**
     * @return bool
     */
    public function canRefund(): bool
    {
        return $this->isSuccess() && $this->isPurchase();
    }

    /**
     * @return bool
     */
    public function canVoidPurchase(): bool
    {
        return $this->isSuccess() && $this->isPurchase();
    }

    /**
     * @return bool
     */
    public function canVoidAuthorization(): bool
    {
        return $this->isSuccess() && $this->isAuthorization();
    }

    /**
     * @return bool
     */
    public function canCapture(): bool
    {
        return $this->isSuccess() && $this->isAuthorization();
    }

    public function getPurchaseAction(): string
    {
        return config('4myth-getaway.actions.purchase', 1);
    }

    public function getRefundAction(): string
    {
        return config('4myth-getaway.actions.refund', 2);
    }

    public function getVoidPurchaseAction(): string
    {
        return config('4myth-getaway.actions.void_purchase', 3);
    }

    public function getAuthorizationAction(): string
    {
        return config('4myth-getaway.actions.authorization', 4);
    }

    public function getCaptureAction(): string
    {
        return config('4myth-getaway.actions.capture', 5);
    }

    public function getVoidRefundAction(): string
    {
        return config('4myth-getaway.actions.void_refund', 6);
    }

    public function getVoidAuthorizationAction(): string
    {
        return config('4myth-getaway.actions.void_authorization', 9);
    }

    public function getTransactionInquiryAction(): string
    {
        return config('4myth-getaway.actions.transaction_inquiry', 10);
    }
}
<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\PaymentGetaway;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use InvalidArgumentException;
use Myth\LaravelTools\Models\Getaway\GetawayOrder;
use Myth\LaravelTools\Models\Getaway\GetawayTransaction;
use Myth\LaravelTools\Utilities\PaymentGetaway\GetawayTransactionResult;

/**
 * @property GetawayOrder $getawayOrder
 * @property GetawayOrder[] $getawayOrders
 */
trait HasGetawayTrait
{
    /**
     * @return string
     */
    public static function getDefaultOrderAction(): string
    {
        return config('4myth-getaway.actions.purchase');
    }

    /**
     * @return MorphOne
     */
    public function getawayOrder(): MorphOne
    {
        return $this->morphOne(config('4myth-getaway.order_class', GetawayOrder::class), config('4myth-getaway.morph_name', 'trackable'))->oldest();
    }

    /**
     * @return MorphMany
     */
    public function getawayOrders(): MorphMany
    {
        return $this->morphMany(config('4myth-getaway.order_class', GetawayOrder::class), config('4myth-getaway.morph_name', 'trackable'));
    }

    /**
     * @param array $attributes
     * @param array $metaData
     * @return GetawayOrder
     */
    public function createGetawayOrder(array $attributes, array $metaData = []): GetawayOrder
    {
        // if ($order = $this->getawayOrder) {
        //     return $order;
        // }
        $action = $attributes['action'] ?? static::getDefaultOrderAction();
        $amount = $attributes['amount'] ?? '0.00';
        $actions = config('4myth-getaway.transaction_class', GetawayTransaction::class)::getTransactionActions();
        if (!in_array($action, $actions)) {
            throw new InvalidArgumentException("Invalid action passed [$action]. Must One Of ".implode(',', $actions));
        }
        /** @var GetawayOrder $order */
        $order = $this->getawayOrders()->create([
            'reference_id'   => null,
            'action'         => (string) $action,
            'status'         => GetawayOrder::statuses('initial'),
            'amount'         => (string) $amount,
            'meta_data'      => $metaData,
            'card_brand'     => null,
            'payment_type'   => null,
            'processed'      => !1,
            'paid_at'        => null,
            'trackable_data' => [],
            'date'           => $attributes['date'] ?? now(),
            'first_name'     => $attributes['first_name'] ?? null,
            'last_name'      => $attributes['last_name'] ?? null,
            'email'          => $attributes['email'] ?? null,
            'mobile'         => $attributes['mobile'] ?? null,
            'address'        => $attributes['address'] ?? null,
            'city'           => $attributes['city'] ?? null,
            'state'          => $attributes['state'] ?? null,
            'zip'            => $attributes['zip'] ?? null,
            'language'       => $attributes['language'] ?? null,
            'description'    => $attributes['description'] ?? null,
        ]);
        return $order;
    }

    /**
     * @param $id
     * @return void
     */
    public function setGetawayReferenceId($id): void
    {
        $this->getawayOrder?->update(['reference_id' => $id]);
    }

    /**
     * Check from order if is initiated or not, and has reference id
     * @return bool
     */
    public function hasPendingOrder(): bool
    {
        return $this->getawayOrders()->where('status', GetawayOrder::statuses('initial'))->whereNotNull(['reference_id'])->exists();
    }

    /**
     * @param GetawayTransaction $item
     * @param GetawayTransactionResult $transaction
     * @return void
     */
    public function onRefund(GetawayTransaction $item, GetawayTransactionResult $transaction): void
    {
    }
}

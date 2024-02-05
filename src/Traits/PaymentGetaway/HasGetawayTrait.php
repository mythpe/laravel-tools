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
     * @param array $data
     * @param string|null $action
     * @param string|null $amount
     * @param string|null $referenceId
     * @param array $metaData
     * @param array $trackableData
     * @return GetawayOrder
     */
    public function createGetawayOrder(array $data = [], ?string $action = null, ?string $amount = null, ?string $referenceId = null, array $metaData = [], array $trackableData = []): GetawayOrder
    {
        if ($order = $this->getawayOrder) {
            return $order;
        }
        $action = $action ?? static::getDefaultOrderAction();
        $actions = config('4myth-getaway.order_class', GetawayOrder::class)::getOrderActions();
        if (!in_array($action, $actions)) {
            throw new InvalidArgumentException('Invalid action argument. Must One Of '.implode(',', $actions));
        }
        $amount = $amount ?? $this->amount;
        $attributes = [
            'reference_id'   => $referenceId,
            'action'         => $action,
            'status'         => 'initial',
            'amount'         => $amount,
            'meta_data'      => $metaData,
            'card_brand'     => null,
            'payment_type'   => null,
            'processed'      => !1,
            'paid_at'        => null,
            'trackable_data' => $trackableData,
            'date'           => now(),
            'first_name'     => $data['first_name'] ?? null,
            'last_name'      => $data['last_name'] ?? null,
            'email'          => $data['email'] ?? null,
            'mobile'         => $data['mobile'] ?? null,
            'address'        => $data['address'] ?? null,
            'city'           => $data['city'] ?? null,
            'state'          => $data['state'] ?? null,
            'zip'            => $data['zip'] ?? null,
            'language'       => $data['language'] ?? null,
            'description'    => $data['description'] ?? null,
        ];
        $order = $this->getawayOrder()->create($attributes);
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

}

<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Models\Getaway;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Myth\LaravelTools\Models\BaseModel;

/**
 *
 * @property int $getaway_order_id
 * @property string $transaction_id
 * @property string $action
 * @property string $action_to_string
 * @property double $amount
 * @property string $result
 * @property string $response_code
 * @property array $meta_data
 * @property ?string $description
 * @property ?string $description_to_string
 * @property ?string $auth_code
 * @property GetawayOrder $order
 */
class GetawayTransaction extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'getaway_order_id',
        'transaction_id',
        'action',
        'amount',
        'result',
        'response_code',
        'meta_data',
        'description',
        'auth_code',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'getaway_order_id' => null,
        'transaction_id'   => null,
        'action'           => null,
        'amount'           => 0.00,
        'result'           => null,
        'response_code'    => null,
        'meta_data'        => '[]',
        'description'      => null,
        'auth_code'        => null,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'getaway_order_id' => 'int',
        'transaction_id'   => 'string',
        'action'           => 'string',
        'amount'           => 'decimal:2',
        'result'           => 'string',
        'response_code'    => 'string',
        'meta_data'        => 'array',
    ];

    /**
     * @return BelongsTo
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(config('4myth-getaway.order_class', GetawayOrder::class), 'getaway_order_id');
    }

    /**
     * @param Builder $builder
     * @param $value
     * @return Builder
     */
    public function scopeByTransactionId(Builder $builder, $value): Builder
    {
        if (!is_array($value)) {
            $value = explode(',', $value);
        }
        return $builder->whereIn('transaction_id', $value);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeSuccessOnly(Builder $builder): Builder
    {
        return $builder->where('response_code', '=', '000');
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
     * $this->description_to_string
     * @return ?string
     */
    public function getDescriptionToStringAttribute(): ?string
    {
        if (($d = $this->description) && trans_has($d, $this->order->language, !0)) {
            return __($d, [
                'id'           => $this->order->id,
                'trackable_id' => $this->order->trackable_id,
                'name'         => $this->order->name,
                'email'        => $this->order->email,
                'mobile'       => $this->order->mobile,
                'amount'       => $this->amount,
            ]);
        }
        return $this->description;
    }

    /**
     * $this->action_to_string
     * @return string
     */
    public function getActionToStringAttribute(): string
    {
        if (!$this->action) {
            return '';
        }
        $value = (array_flip(GetawayOrder::getOrderActions())[$this->action] ?? $this->action) ?: $this->action;
        return trans_has(($k = "const.getaway_actions.$value"), strtolower($this->order->language), !0) ? __($k) : $value;
    }
}

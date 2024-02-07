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
use Illuminate\Support\Str;
use Myth\LaravelTools\Models\BaseModel;
use Myth\LaravelTools\Traits\PaymentGetaway\GetawayActionsTrait;
use Myth\LaravelTools\Utilities\PaymentGetaway\GetawayApi;
use Myth\LaravelTools\Utilities\PaymentGetaway\GetawayInquiryResult;
use Myth\LaravelTools\Utilities\PaymentGetaway\GetawayTransactionResult;

/**
 *
 * @property int $getaway_order_id
 * @property string $transaction_id
 * @property string $track_id
 * @property string $action
 * @property string $action_to_string
 * @property float $amount
 * @property float $outstanding_amount
 * @property string $result
 * @property string $result_to_string
 * @property string $response_code
 * @property array $meta_data
 * @property ?string $description
 * @property ?string $description_to_string
 * @property ?string $auth_code
 * @property string $response_code_message
 * @property bool $used
 * @property string $used_to_string
 * @property GetawayOrder $order
 * @method static Builder byTransactionId(string|array $id)
 * @method static Builder successOnly()
 * @method static Builder usedOnly()
 * @method static Builder notUsedOnly()
 */
class GetawayTransaction extends BaseModel
{
    use SoftDeletes;
    use GetawayActionsTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'getaway_order_id',
        'transaction_id',
        'track_id',
        'action',
        'amount',
        'result',
        'response_code',
        'meta_data',
        'description',
        'auth_code',
        'used',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'getaway_order_id' => null,
        'transaction_id'   => null,
        'track_id'         => null,
        'action'           => null,
        'amount'           => 0.00,
        'result'           => null,
        'response_code'    => null,
        'meta_data'        => '[]',
        'description'      => null,
        'auth_code'        => null,
        'used'             => !1,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'getaway_order_id' => 'int',
        'transaction_id'   => 'string',
        'track_id'         => 'string',
        'action'           => 'string',
        'amount'           => 'decimal:2',
        'result'           => 'string',
        'response_code'    => 'string',
        'meta_data'        => 'array',
        'used'             => 'bool',
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
    public function scopeUsedOnly(Builder $builder): Builder
    {
        return $builder->where('used', '=', !0);
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeNotUsedOnly(Builder $builder): Builder
    {
        return $builder->where('used', '=', !1);
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
        $actions = array_flip(static::getTransactionActions());
        $value = ($actions[$this->action] ?? $this->action) ?: $this->action;
        return trans_has(($k = "const.getaway_actions.$value"), strtolower($this->order->language), !0) ? __($k) : $value;
    }

    /**
     * $this->used_to_string
     * @return string
     */
    public function getUsedToStringAttribute(): string
    {
        return $this->used_to_yes ?: '';
    }

    /**
     * @param string|null $inquiryType
     * @return GetawayInquiryResult
     */
    public function inquiry(?string $inquiryType = null): GetawayInquiryResult
    {
        return GetawayApi::inquiry($this->order->reference_id, $this->track_id, $this->amount, $inquiryType);
    }

    public function createTransaction($action, ?string $amount = null, ?string $description = null, ?array $metaData = null, ?array $customer = null): GetawayTransactionResult
    {
        $amount = $amount ?: $this->amount;
        $transaction = GetawayApi::transaction($this->track_id, $amount, $this->order->email, $action, $this->transaction_id, $metaData, $customer);
        $newUsed = !$transaction->success;
        $isInquiry = $action == static::getTransactionInquiryAction();
        if ($transaction->success) {
            if (in_array($action, [$this->actionsCantDoTransaction(), $this->getCaptureAction()])) {
                $newUsed = !0;
            }
        }
        if (!$isInquiry) {
            if (!$transaction->amount) {
                $description = $description ?: '';
                $description .= '. No Amount.';
            }
            /** @var GetawayTransaction $newTraction */
            $newTraction = $this->order->transactions()->create([
                'transaction_id' => $transaction->tranid,
                'track_id'       => $transaction->trackid,
                'action'         => $action,
                'amount'         => $transaction->amount ?: $amount,
                'result'         => $transaction->result,
                'response_code'  => $transaction->responseCode,
                'auth_code'      => $transaction->authcode,
                'description'    => $description,
                'meta_data'      => $transaction->request,
                'used'           => $newUsed,
            ]);
            if ($transaction->success) {
                if (in_array($action, $this->actionsCantDoTransaction())) {
                    $this->used = !0;
                    $this->save();
                }
                if ($action == static::getRefundAction()) {
                    $outstandingAmount = $this->order->getOutstandingAmount();
                    $this->order->status = $outstandingAmount > 0 ? GetawayOrder::statuses('partial_refund') : GetawayOrder::statuses('refunded');
                    $this->order->save();
                    if ($this->order->isRefunded()) {
                        // $newTraction
                        // $this->order->transactions()->update(['used' => !0]);
                        $this->order->firstTransaction()->update(['used' => !1]);
                    }
                }
                if ($action == static::getVoidRefundAction()) {
                    $outstandingAmount = $this->order->getOutstandingAmount();
                    // if ($outstandingAmount == $this->order->amount) {
                    $this->order->status = $outstandingAmount == $this->order->amount ? GetawayOrder::statuses('paid') : GetawayOrder::statuses('partial_refund');
                    $this->order->save();
                    // }
                }
            }
        }
        return $transaction;
    }

    /**
     * @param string|null $amount
     * @param string|null $description
     * @return GetawayTransactionResult
     */
    public function refund(?string $amount = null, ?string $description = null): GetawayTransactionResult
    {
        return $this->createTransaction(static::getRefundAction(), $amount, $description);
    }

    /**
     * @param string|null $description
     * @return GetawayTransactionResult
     */
    public function voidRefund(?string $description = null): GetawayTransactionResult
    {
        return $this->createTransaction(static::getVoidRefundAction(), $this->amount, $description);
    }

    /**
     * @param string|null $description
     * @return GetawayTransactionResult
     */
    public function capture(?string $description = null): GetawayTransactionResult
    {
        return $this->createTransaction(static::getCaptureAction(), $this->amount, $description);
    }

    /**
     * @param ?string $description
     * @return GetawayTransactionResult
     */
    public function voidAuthorization(?string $description = null): GetawayTransactionResult
    {
        return $this->createTransaction(config('4myth-getaway.actions.void_authorization'), description : $description);
    }

    /**
     * $this->result_to_string
     * @return string
     */
    public function getResultToStringAttribute(): string
    {
        return trans_has($k = 'const.statuses.'.Str::snake($this->result)) ? __($k) : $this->result;
    }

    /**
     * $this->response_code_message
     * @return string
     */
    public function getResponseCodeMessageAttribute(): string
    {
        return config('4myth-getaway.codes.'.$this->response_code, $this->result) ?: '';
    }

    public function isUsed(): bool
    {
        return $this->used;
    }

    /**
     * @return float
     */
    public function getOutstandingAmount(): float
    {
        if (!$this->exists || (!$this->isAuthorization() && !$this->isPurchase())) {
            return 0.0;
        }
        return $this->order->getOutstandingAmount();
        // return $this->amount - floatval($this->order->transactions()->refundOnly()->successOnly()->notUsedOnly()->sum('amount') ?: 0);
    }

    /**
     * $this->outstanding_amount
     * @return float
     */
    public function getOutstandingAmountAttribute(): float
    {
        return $this->getOutstandingAmount();
    }
}

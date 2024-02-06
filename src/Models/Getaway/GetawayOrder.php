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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Myth\LaravelTools\Models\BaseModel;
use Myth\LaravelTools\Utilities\PaymentGetaway\GetawayApi;
use Myth\LaravelTools\Utilities\PaymentGetaway\GetawayControllerApi;
use Myth\LaravelTools\Utilities\PaymentGetaway\GetawayInquiryResult;
use Myth\LaravelTools\Utilities\PaymentGetaway\GetawayTransactionResult;

/**
 * @property ?string $reference_id
 * @property ?string $track_id
 * @property string $action
 * @property string $action_to_string
 * @property ?string $status
 * @property ?string $status_to_string
 * @property double $amount
 * @property array $meta_data
 * @property ?string $card_brand
 * @property ?string $payment_type
 * @property bool $processed
 * @property ?Carbon $paid_at
 * @property ?int $trackable_id
 * @property ?string $trackable_type
 * @property array $trackable_data
 * @property Carbon $date
 * @property string $name
 * @property string $first_name
 * @property ?string $last_name
 * @property string $email
 * @property ?string $mobile
 * @property ?string $address
 * @property ?string $city
 * @property ?string $state
 * @property ?string $zip
 * @property ?string $language
 * @property ?string $description
 * @property ?string $description_to_string
 * @property ?BaseModel $trackable
 * @method static Builder initialOnly()
 * @method static Builder paidOnly()
 * @method static Builder failedOnly()
 * @method static Builder unSuccessfulOnly()
 */
class GetawayOrder extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reference_id',
        'track_id',
        'action',
        'status',
        'amount',
        'meta_data',
        'card_brand',
        'payment_type',
        'processed',
        'paid_at',
        'trackable_id',
        'trackable_type',
        'trackable_data',
        'date',
        'first_name',
        'last_name',
        'email',
        'mobile',
        'address',
        'city',
        'state',
        'zip',
        'language',
        'description',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'reference_id'   => null,
        'track_id'       => null,
        'action'         => null,
        'status'         => null,
        'amount'         => 0.00,
        'meta_data'      => null,
        'card_brand'     => null,
        'payment_type'   => null,
        'processed'      => !1,
        'paid_at'        => null,
        'trackable_id'   => null,
        'trackable_type' => null,
        'trackable_data' => null,
        'date'           => null,
        'first_name'     => null,
        'last_name'      => null,
        'email'          => null,
        'mobile'         => null,
        'address'        => null,
        'city'           => null,
        'state'          => null,
        'zip'            => null,
        'language'       => null,
        'description'    => null,
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'reference_id'   => 'string',
        'track_id'       => 'string',
        'action'         => 'string',
        'amount'         => 'decimal:2',
        'meta_data'      => 'array',
        'processed'      => 'bool',
        'paid_at'        => 'datetime',
        'trackable_id'   => 'int',
        'trackable_data' => 'array',
        'date'           => 'datetime',
    ];

    /**
     * @param string|null $key
     * @return array|string
     */
    public static function statuses(?string $key = null): array | string
    {
        $statuses = config('4myth-getaway.statuses', []);
        if ($key && !array_key_exists($key, $statuses)) {
            throw new InvalidArgumentException("Invalid key passed $key. Must One Of ".implode(',', array_keys($statuses)));
        }
        return !is_null($key) ? $statuses[$key] : $statuses;
    }

    /**
     * @return string
     */
    public static function trackPrefix(): string
    {
        return config('4myth-getaway.order_track_prefix', '');
    }

    /**
     * @param string $value
     * @return ?$this
     */
    public static function byTrackId(string $value): ?self
    {
        return self::query()->find(Str::afterLast($value, static::trackPrefix()));
    }

    public function isInitial(): bool
    {
        return $this->status == static::statuses('initial');
    }

    public function isPaid(): bool
    {
        return $this->status == static::statuses('paid');
    }

    public function isFailed(): bool
    {
        return $this->status == static::statuses('failed');
    }

    public function isUnSuccessful(): bool
    {
        return $this->status == static::statuses('un_successful');
    }

    public function isRefunded(): bool
    {
        return $this->status == static::statuses('refunded');
    }

    public function isPartialRefund(): bool
    {
        return $this->status == static::statuses('partial_refund');
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeInitialOnly(Builder $builder): Builder
    {
        return $builder->where('status', '=', static::statuses('initial'));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopePaidOnly(Builder $builder): Builder
    {
        return $builder->where('status', '=', static::statuses('paid'));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeFailedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '=', static::statuses('failed'));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeUnSuccessfulOnly(Builder $builder): Builder
    {
        return $builder->where('status', '=', static::statuses('un_successful'));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopeRefundedOnly(Builder $builder): Builder
    {
        return $builder->where('status', '=', static::statuses('refunded'));
    }

    /**
     * @param Builder $builder
     * @return Builder
     */
    public function scopePartialRefundOnly(Builder $builder): Builder
    {
        return $builder->where('status', '=', static::statuses('partial_refund'));
    }

    /**
     * Make attributes hidden fro array
     * @return string[]
     */
    public function defaultHiddenAttributes(): array
    {
        return array_merge(parent::defaultHiddenAttributes(), ['meta_data', 'trackable_data']);
    }

    /**
     * @param $value
     * @return string|null
     */
    public function getNameAttribute($value): ?string
    {
        $firstName = $this->first_name ?: '';
        $lastName = $this->last_name ?: '';
        return trim("$firstName $lastName");
    }

    /**
     * @param $value
     * @return void
     */
    public function setNameAttribute($value): void
    {
        $value = $value ?: '';
        $firstName = Str::before($value, ' ');
        $lastName = Str::after($value, ' ');
        $this->first_name = $firstName;
        $this->last_name = $lastName;
    }

    /**
     * $this->description_to_string
     * @return ?string
     */
    public function getDescriptionToStringAttribute(): ?string
    {
        if (($d = $this->description) && trans_has($d, $this->language, !0)) {
            return __($d, [
                'id'           => $this->id,
                'trackable_id' => $this->trackable_id,
                'name'         => $this->name,
                'email'        => $this->email,
                'mobile'       => $this->mobile,
                'amount'       => $this->amount,
            ]);
        }
        return $this->description;
    }

    /**
     * $this->status_to_string
     * @return ?string
     */
    public function getStatusToStringAttribute(): ?string
    {
        if (!$this->status) {
            return null;
        }
        if (trans_has(($k = "const.statuses.".Str::snake($this->status)), strtolower($this->language), !0)) {
            return __($k);
        }
        return $this->status;
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
        $value = (array_flip(GetawayTransaction::getTransactionActions())[$this->action] ?? $this->action) ?: $this->action;
        return trans_has(($k = "const.getaway_actions.$value"), strtolower($this->language), !0) ? __($k) : $value;
    }

    /**
     * @return bool
     */
    public function isProcessed(): bool
    {
        return $this->processed;
    }

    /**
     * @return MorphTo
     */
    public function trackable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(config('4myth-getaway.transaction_class', GetawayTransaction::class));
    }

    /**
     * @param $id
     * @return void
     */
    public function setReferenceId($id): void
    {
        $this->reference_id = $id;
        $this->save();
    }

    /**
     * Generate new track id
     * @return string
     */
    public function trackId(): string
    {
        return static::trackPrefix().$this->id;
    }

    /**
     * @param array $data
     * @param array $metaData
     * @param array $customerData
     * @return GetawayTransactionResult
     */
    public function createGetawayTransaction(array $data = [], array $metaData = [], array $customerData = []): GetawayTransactionResult
    {
        $action = $this->action;
        $amount = $this->amount;
        $email = $data['email'] ?? $this->email;
        $api = GetawayApi::instance();
        $api->language = $data['language'] ?? ($this->language ?: $api->language);
        $transId = $data['transaction_id'] ?? null;
        if ($this->isProcessed()) {
            return new class extends GetawayTransactionResult {
            };
        }

        $trackId = $this->trackId();
        if (!$this->track_id) {
            $this->track_id = $trackId;
        }
        $transaction = $api->transaction($trackId, "$amount", $email, $action, $transId, $metaData, $customerData);
        if ($transaction->payid && $transaction->payid != $this->reference_id) {
            $this->reference_id = $transaction->payid;
        }
        $response = $transaction->request;
        foreach (['terminalId', 'apiKey', 'password'] as $key => $value) {
            if (array_key_exists($key, $response)) {
                unset($response[$key]);
            }
        }
        $this->meta_data = array_merge($this->meta_data, $response);
        if (!$transaction->success) {
            $this->status = static::statuses('un_successful');
        }
        $this->save();
        return $transaction;
    }

    /**
     * @param GetawayControllerApi $controller
     * @return GetawayTransaction
     */
    public function processTransactionResponse(GetawayControllerApi $controller): GetawayTransaction
    {
        /** @var GetawayTransaction $transaction */
        $transaction = $this->transactions()->make([
            'transaction_id' => $controller->data->TranId,
            'track_id'       => $controller->data->TrackId,
            'action'         => $this->action,
            'amount'         => $controller->data->amount,
            'result'         => $controller->data->Result,
            'response_code'  => $controller->data->ResponseCode,
            'meta_data'      => array_merge($controller->data->toArray(), ['metaData' => $controller->metaData]),
            'description'    => $this->description,
            'auth_code'      => $controller->data->AuthCode,
        ]);
        $transaction->save();
        $this->meta_data = array_merge($this->meta_data, $controller->metaData);
        if ($paid = $controller->data->ResponseCode == '000') {
            $this->paid_at = now();
        }
        $this->status = static::statuses($paid ? 'paid' : 'failed');
        $this->reference_id = $controller->data->PaymentId;
        $this->track_id = $controller->data->TrackId;
        $this->card_brand = $controller->data->cardBrand;
        $this->payment_type = $controller->data->PaymentType;
        $this->processed = !0;
        $this->save();
        return $transaction;
    }

    /**
     * @param string|null $inquiryType
     * @return GetawayInquiryResult
     */
    public function inquiry(?string $inquiryType = null): GetawayInquiryResult
    {
        /** @var GetawayTransaction $transaction */
        if (!($transaction = $this->transactions()->where('transaction_id', $this->reference_id)->first())) {
            return new class extends GetawayInquiryResult {
            };
        }
        return $transaction->inquiry($inquiryType);
    }

    /**
     * @param string|null $amount
     * @param string|null $description
     * @param array|null $metaData
     * @param array|null $customer
     * @return GetawayTransactionResult
     */
    public function refund(?string $amount = null, ?string $description = null, ?array $metaData = null, ?array $customer = null): GetawayTransactionResult
    {
        if (!$this->reference_id || !$this->isPaid()) {
            return new class extends GetawayTransactionResult {
            };
        }
        $amount = $amount ?: $this->amount;
        $action = config('4myth-getaway.actions.refund');
        $transaction = GetawayApi::transaction($this->track_id, $amount, $this->email, $action, $this->reference_id, $metaData, $customer);
        /** @var GetawayTransaction $item */
        $item = $this->transactions()->create([
            'transaction_id' => $transaction->tranid,
            'track_id'       => $transaction->trackid,
            'action'         => $action,
            'amount'         => $transaction->amount,
            'result'         => $transaction->result,
            'response_code'  => $transaction->responseCode,
            'auth_code'      => $transaction->authcode,
            'description'    => $description,
            'meta_data'      => array_merge($transaction->request, [
                'metaData' => array_merge($transaction->metaData(), $metaData ?: []),
            ]),
        ]);
        if ($transaction->success) {
            $this->status = $amount == $this->amount ? static::statuses('refunded') : static::statuses('partial_refund');
            $this->save();
        }
        if ($this->trackable && method_exists($this->trackable, 'onRefund')) {
            try {
                $this->trackable->onRefund($item, $transaction);
            }
            catch (\Exception $exception) {
            }
        }
        return $transaction;
    }

    /**
     * @return float
     */
    public function getOutstandingAmount(): float
    {
        return $this->amount - floatval($this->transactions()->refundOnly()->successOnly()->sum('amount') ?: 0);
    }
}

<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Models\Getaway;

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
     * @return array
     */
    public static function getOrderActions(): array
    {
        return config('4myth-getaway.actions', []);
    }

    /**
     * @return array
     */
    public static function getOrderInquiryTypes(): array
    {
        return config('4myth-getaway.inquiry_types', []);
    }

    /**
     * @param string|null $key
     * @return array|string
     */
    public static function statuses(?string $key = null): array | string
    {
        $statuses = config('4myth-getaway.statuses', []);
        if (!array_key_exists($key, $statuses)) {
            throw new InvalidArgumentException('Invalid key argument. Must One Of '.implode(',', array_keys($statuses)));
        }
        return !is_null($key) ? $statuses[$key] : $statuses;
    }

    /**
     * @return string
     */
    public static function trackPrefix(): string
    {
        return 'RN-';
    }

    /**
     * @param string $value
     * @return ?$this
     */
    public static function byTrackId(string $value): ?self
    {
        return self::query()->find(Str::afterLast($value, static::trackPrefix()));
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
        if (trans_has($this->description, strtolower($this->language), !0)) {
            return __($this->description, [
                'id'         => $this->id,
                'payable_id' => $this->payable_id,
                'name'       => $this->name,
                'email'      => $this->email,
                'mobile'     => $this->mobile,
                'amount'     => $this->amount,
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
        if (trans_has(($k = "const.statuses.$this->status"), strtolower($this->language), !0)) {
            return __($k);
        }
        return $this->status;
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
        $transId = null;
        if ($this->isProcessed()) {
            return new class extends GetawayTransactionResult {
            };
        }
        $transaction = $api->transaction($this->trackId(), "$amount", $email, $action, $transId, $metaData, $customerData);
        if ($transaction->payid && $transaction->payid != $this->reference_id) {
            $this->reference_id = $transaction->payid;
            $this->save();
        }
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
            'action'         => $this->action,
            'amount'         => $controller->data->amount,
            'result'         => $controller->data->Result,
            'response_code'  => $controller->data->ResponseCode,
            'meta_data'      => array_merge($controller->data->toArray(), ['metaData' => $controller->metaData]),
            'description'    => $this->description,
            'auth_code'      => $controller->data->AuthCode,
        ]);
        // dd($transaction);
        $transaction->save();
        $this->meta_data = array_merge($this->meta_data, $controller->metaData);
        if ($paid = $controller->data->ResponseCode == '000') {
            $this->paid_at = now();
        }
        $this->status = static::statuses($paid ? 'paid' : 'failed');
        $this->track_id = $controller->data->TrackId;
        $this->card_brand = $controller->data->cardBrand;
        $this->payment_type = $controller->data->PaymentType;
        $this->processed = !0;
        $this->save();
        return $transaction;
    }

    /**
     * @return GetawayInquiryResult
     */
    public function inquiry(): GetawayInquiryResult
    {
        if (!$this->reference_id) {
            return new class extends GetawayInquiryResult {
            };
        }
        return GetawayApi::inquiry($this->reference_id, $this->track_id ?: $this->trackId(), $this->amount);
    }
}

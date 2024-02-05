<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Models\Getaway;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Myth\LaravelTools\Models\BaseModel;

/**
 *
 * @property int $getaway_order_id
 * @property string $transaction_id
 * @property string $action
 * @property double $amount
 * @property string $result
 * @property string $response_code
 * @property array $meta_data
 * @property ?string $description
 * @property ?string $auth_code
 */
class GetawayTransaction extends BaseModel
{
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

}

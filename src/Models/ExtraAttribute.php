<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Models;

use Illuminate\Database\Eloquent\Relations\MorphTo;
use Myth\LaravelTools\Traits\Utilities\HasTranslatorTrait;
use Myth\LaravelTools\Traits\Utilities\OrderByScopeTrait;

class ExtraAttribute extends BaseModel
{
    use OrderByScopeTrait, HasTranslatorTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'key',
        'type',
        'required',
        'order_by',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'name'     => null,
        'key'      => null,
        'type'      => null,
        'required' => !1,
        'order_by' => 0,
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'order_by' => 'int',
        'required' => 'bool',
    ];

    /**
     * @param $value
     * @return void
     */
    public function setKeyAttribute($value): void
    {
        $this->attributes['key'] = strtolower($value);
    }

}

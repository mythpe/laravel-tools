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

class Translatable extends BaseModel
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'translatable_id',
        'translatable_type',
        'locale',
        'attribute',
        'value',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'translatable_id'   => null,
        'translatable_type' => null,
        'locale'            => null,
        'attribute'         => null,
        'value'             => null,
    ];

    /**
     * @return MorphTo
     */
    public function translatable(): MorphTo
    {
        return $this->morphTo(config('4myth-tools.translatable_morph'));
    }
}

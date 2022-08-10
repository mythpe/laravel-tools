<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AttributesLog extends BaseModel
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'loggable_id',
        'loggable_type',
        'user_id',
        'attribute',
        'old_value',
        'new_value',
    ];

    /**
     * The model's attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'user_id'   => null,
        'attribute' => null,
        'old_value' => null,
        'new_value' => null,
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function loggable(): MorphTo
    {
        return $this->morphTo(config('4myth-tools.attributes_log_morph'));
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('4myth-tools.user_class'))->withDefault();
    }

    /**
     * @return string
     */
    public function getRawText(): string
    {
        $old = trim($this->old_value ?: '');
        $new = trim($this->new_value ?: '');
        if ($old && $new) {
            return "[$old] ==> [$new]";
        }
        return $old ?: ($new ?: '');
    }
}

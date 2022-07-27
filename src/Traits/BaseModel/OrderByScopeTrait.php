<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\BaseModel;

use Myth\LaravelTools\Scopes\OrderByScope;

trait OrderByScopeTrait
{
    public static function bootOrderByScopeTrait()
    {
        $columns = static::getScopeOrderByColumns();
        static::addGlobalScope(OrderByScope::make($columns, static::scopeOrderByDirection()));
    }

    /**
     * [ column:string ]
     * [ column:string => direction:string (asc|desc) ]
     *
     * @return array<int|string,string>
     */
    public static function getScopeOrderByColumns(): array
    {
        return ['order_by' => 'asc'];
    }

    /**
     * Global direction of order. (asc|desc)
     *
     * @return string
     */
    public static function scopeOrderByDirection(): string
    {
        return 'asc';
    }
}

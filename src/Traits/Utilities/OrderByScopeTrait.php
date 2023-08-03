<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Myth\LaravelTools\Scopes\OrderByScope;

trait OrderByScopeTrait
{
    public static function bootOrderByScopeTrait(): void
    {
        $columns = static::getScopeOrderByColumns();
        if (!in_array(request()->input('fdt'), ['i', 'e', 's', 'u', 'd'])) {
            self::addGlobalScope(OrderByScope::make($columns, self::scopeOrderByDirection()));
        }
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

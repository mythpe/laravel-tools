<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Rules;

use Exception;
use Illuminate\Support\Carbon;

class AppRules
{
    /**
     * @return string
     */
    public static function timeFormat(): string
    {
        return 'date_format:'.config('4myth-tools.date_format.time');
    }

    /**
     * @return string
     */
    public static function dateFormat(): string
    {
        return 'date_format:'.config('4myth-tools.date_format.date');
    }

    /**
     * @param Carbon|string|null $date
     *
     * @return string
     */
    public static function timeAfterOrEqual(Carbon | string $date = null): string
    {
        return static::dateAfterOrEqual($date, 'time');
    }

    /**
     * @param Carbon|string|null $date
     * @param string $configFormat
     *
     * @return string
     */
    public static function dateAfterOrEqual(Carbon | string $date = null, string $configFormat = 'date'): string
    {
        $format = config("4myth-tools.date_format.{$configFormat}");
        $request = request();
        if (!$date instanceof Carbon && $request->has($date)) {
            return "after_or_equal:$date";
        }
        try {
            $date = $date ? ($request->has($date) ? Carbon::make($request->input($date, now())) : Carbon::make($date)) : now();
        }
        catch (Exception $exception) {
            $date = $request->input($date);
            return "after_or_equal:$date";
        }
        return "after_or_equal:{$date->format($format)}";
    }

    /**
     * @param Carbon|string|null $date
     * @param string $configFormat
     *
     * @return string
     */
    public static function dateBeforeOrEqual(Carbon | string $date = null, string $configFormat = 'date'): string
    {
        $format = config("4myth-tools.date_format.{$configFormat}");
        $request = request();
        if (!$date instanceof Carbon && $request->has($date)) {
            return "before_or_equal:$date";
        }
        try {
            $date = $date ? ($request->has($date) ? Carbon::make($request->input($date, now())) : Carbon::make($date)) : now();
        }
        catch (Exception $exception) {
            $date = $request->input($date);
            return "before_or_equal:$date";
        }
        return "before_or_equal:{$date->format($format)}";
    }
}

<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Illuminate\Support\Str;

trait LastStrTrait
{
    /**
     * @param int $length
     * @param string $attribute
     * @return string
     */
    public function getLastStr(int $length = 4, string $attribute = 'mobile', string $prefix = '#'): string
    {
        $value = $this->{$attribute};
        if (!$value) {
            return '';
        }
        if (filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $email = explode('@', $value);
            $first = $email[0] ?? '';
            $last = Str::beforeLast($email[1] ?? '', '.');
            return substr($first, 0, 2)."**@".substr($last, 0, 2).'****';
        }
        $padLength = strlen($value) <= $length ? 4 : strlen($value) - $length;
        $pad = str_pad('', $padLength, $prefix);
        return $pad.substr($value, -$length);
    }
}

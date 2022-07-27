<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\Utilities;

use Illuminate\Support\Str;

trait LastStrTrait
{
    /**
     * @param  int  $length
     *
     * @return string
     */
    public function mobileLastStr(int $length = 4): string
    {
        if (!$this->mobile) {
            return '';
        }
        return 'xxxx'.substr($this->mobile, -$length);
    }

    /**
     * @return string
     */
    public function emailLastStr(): string
    {
        if (!$this->email) {
            return '';
        }
        $email = explode('@', $this->email);
        $first = ($email[0] ?? '');
        $last = Str::beforeLast(($email[1] ?? ''), '.');
        $end = substr(Str::afterLast($this->email, '.'), 0);
        return substr($first, 0, 2).'**@'.substr($last, 0, 2).'**.'.$end;
    }
}

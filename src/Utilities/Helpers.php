<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities;

use Illuminate\Support\Str;

class Helpers
{
    /**
     * helper
     *
     * @param $str
     *
     * @return array
     */
    public static function strCasesArray($str): array
    {
        return collect([
            $str,
            Str::snake($str),
            Str::camel($str),
            Str::kebab($str),
        ])->uniqueStrict()->toArray();
    }

    /**
     * Object uses trait
     * @param $objectOrString
     * @param $traitOrString
     * @return bool
     */
    public static function hasTrait($objectOrString, $traitOrString): bool
    {
        if (($uses = class_uses($objectOrString))) {
            return in_array($traitOrString, array_keys($uses));
        }
        return !1;
    }
}
<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

use App\Http\Middleware\PermissionMiddleware;
use GeniusTS\HijriDate\Date;
use GeniusTS\HijriDate\Hijri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Support\Str;
use Symfony\Component\VarDumper\VarDumper;

if (!function_exists('to_number_format')) {
    /**
     * @param  string|int|float  $number
     * @param  int  $decimals
     * @param  string  $currency
     * @param  string  $thousands_sep
     * @param  string  $dec_point
     *
     * @return string
     */
    function to_number_format($number, $decimals = 2, $currency = null, $thousands_sep = ',', $dec_point = '.')
    {
        $v = number_format((float) $number, (int) $decimals, $dec_point, $thousands_sep);
        //$temp = explode('.', $v);
        //$temp[0] = isset($temp[0]) ? $temp[0] : 0;
        //$temp[1] = isset($temp[1]) ? $temp[1] : 0;
        //$args[0] = $temp[0];
        //$res = "{$temp[0]}" . (intval($temp[1]) > 0 ? ".{$temp[1]}" : '');

        return $v.($currency ? " {$currency}" : '');
    }
}

if (!function_exists('ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     *
     * @return bool
     */
    function ends_with($haystack, $needles)
    {
        return Str::endsWith($haystack, $needles);
    }
}

if (!function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param  string  $haystack
     * @param  string|array  $needles
     *
     * @return bool
     */
    function starts_with($haystack, $needles)
    {
        return Str::startsWith($haystack, $needles);
    }
}

if (!function_exists('d')) {
    /**
     * @param  mixed  ...$vars
     */
    function d(...$vars)
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: *');
        header('Access-Control-Allow-Headers: *');
        http_response_code(500);

        $debug = @debug_backtrace();
        $call = current($debug);
        $line = (isset($call['line']) ? $call['line'] : __LINE__);
        $file = (isset($call['file']) ? $call['file'] : __FILE__);

        echo("[{$file}] Line ({$line}): <br>");
        foreach ($vars as $v) {
            VarDumper::dump($v);
        }

        die(1);
    }
}

if (!function_exists('locale_attribute')) {
    /**
     * Get attribute by locale
     *
     * @param  string  $attribute
     * @param  string|null  $locale
     *
     * @return string
     * @uses app()->getLocale()
     */
    function locale_attribute($attribute = "name", $locale = null)
    {
        is_null($locale) && ($locale = app()->getLocale());
        return rtrim($attribute, '_')."_".$locale;
    }
}

if (!function_exists('str_replace_en_ar')) {
    /**
     * Replace string for AR & EN
     *
     * @param  string  $string
     *
     * @return string
     */
    function str_replace_en_ar(string $string = '')
    {
        return str_replace_name_ar(str_replace_name_en($string));
    }
}

if (!function_exists('str_replace_name_ar')) {
    /**
     * Replace string for AR Name
     *
     * @param  string  $string
     *
     * @return string
     */
    function str_replace_name_ar($string = '')
    {
        $string = str_ireplace(['إ', 'أ'], 'ا', $string);
        $string = str_ireplace("عبدال", 'عبد ال', $string);
        return trim($string);
    }
}

if (!function_exists('str_replace_name_en')) {
    /**
     * Replace string for EN Name
     *
     * @param  string  $string
     *
     * @return string
     */
    function str_replace_name_en($string = '')
    {
        $string = trim($string);
        return ucwords($string);
    }
}

if (!function_exists('date_by_locale')) {
    /**
     * Convert date By locale
     *
     * @param $date
     * @param  null  $toLocale
     *
     * @return mixed|string
     */
    function date_by_locale($date, $toLocale = null)
    {
        if (is_null($toLocale)) {
            $toLocale = app()->getLocale();
        }

        $ar = [
            "الأحد",
            "أح",
            "الإثنين",
            "إث",
            "الثلاثاء",
            "ث",
            "الأربعاء",
            "أر",
            "الخميس",
            "خ",
            "الجمعة",
            "ج",
            "السبت",
            "س",
            "ص",
            "ص",
            "م",
            "م",
            "يناير",
            "يناير",
            "فبراير",
            "فبراير",
            "مارس",
            "مارس",
            "أبريل",
            "أبريل",
            "مايو",
            "مايو",
            "يونيو",
            "يونيو",
            "يوليو",
            "يوليو",
            "أغسطس",
            "أغسطس",
            "سبتمبر",
            "سبتمبر",
            "اكتوبر",
            "اكتوبر",
            "نوفمبر",
            "نوفمبر",
            "ديسمبر",
            "ديسمبر",
        ];
        $notAr = [
            "Sunday",
            "Sun",
            "Monday",
            "Mon",
            "Tuesday",
            "Tue",
            "Wednesday",
            "Wed",
            "Thursday",
            "Thu",
            "Friday",
            "Fri",
            "Saturday",
            "Sat",
            "am",
            "AM",
            "pm",
            "PM",
            "January",
            "Jan",
            "February",
            "Feb",
            "March",
            "Mar",
            "April",
            "Apr",
            "May",
            "May",
            "June",
            "Jun",
            "July",
            "Jul",
            "August",
            "Aug",
            "September",
            "Sep",
            "October",
            "Oct",
            "November",
            "Nov",
            "December",
            "Dec",
        ];

        try {
            return str_ireplace($toLocale === 'ar' ? $notAr : $ar, $toLocale === 'ar' ? $ar : $notAr, $date);
        }
        catch (Exception $exception) {
            //if (config('app.debug')) {
            //    d($exception);
            //}
        }

        return $date;
    }
}

if (!function_exists('manifest_directory')) {
    function manifest_directory($path = null)
    {
        $directory = rtrim(config('app.manifest_directory'), '/');
        if (!is_null($path)) {
            $directory .= '/'.ltrim($path, '/');
        }
        return $directory;
    }
}

if (!function_exists('trans_has')) {
    /**
     * Determine if a translation exists.
     *
     * @param  string  $key
     * @param  string|null  $locale
     * @param  bool  $fallback
     *
     * @return bool
     */
    function trans_has($key, $locale = null, $fallback = true)
    {
        return app('translator')->has($key, $locale, $fallback);
    }
}

if (!function_exists('hijri')) {
    /**
     * helper convert to hijri
     *
     * @param  string  $date
     *
     * @return \GeniusTS\HijriDate\Date
     */
    function hijri($date = '')
    {
        if ($date instanceof Date) {
            return $date;
        }
        if (!$date instanceof Carbon) {
            $temp = Carbon::make($date);

            # Hijri
            if ($temp->year < 1990) {
                $ex = explode("-", $date);
                count($ex) < 3 && ($ex = explode("/", $date));

                $year = $temp->year;
                $month = isset($ex[1]) && strlen($ex[1]) == 2 ? $ex[1] : 1;
                $day = strpos("$date", "$year") === 0 && isset($ex[2]) ? $ex[2] : 1;

                $date = Hijri::convertToGregorian($day, $month, $year);
            }
            else {
                $date = $temp;
            }
        }

        return Hijri::convertToHijri($date);
    }
}

if (!function_exists('arabic_date')) {
    /**
     * @param $string
     * @param  string|null|bool  $append
     *
     * @return string
     */
    function arabic_date($string, $append = null)
    {
        $ar = [
            '/',
            '٠',
            '١',
            '٢',
            '٣',
            '٤',
            '٥',
            '٦',
            '٧',
            '٨',
            '٩',
        ];

        $notAr = [
            '-',
            '0',
            '1',
            '2',
            '3',
            '4',
            '5',
            '6',
            '7',
            '8',
            '9',
        ];

        $val = str_ireplace($notAr, $ar, $string);
        return $val.($append ? ($append == !0 ? " هـ" : $append) : '');
    }
}

if (!function_exists('isBase64')) {
    /**
     * Check if string is image base64
     *
     * @param $str
     *
     * @return bool
     */
    function isBase64($str)
    {
        return strpos($str, ';base64') !== false || base64_encode(base64_decode($str, true)) === $str;
    }
}

if (!function_exists('appName')) {
    /**
     * Setting app name
     *
     * @param  string|null  $locale
     *
     * @return string
     */
    function appName(?string $locale = null): string
    {
        return (string) setting(locale_attribute('app_name', $locale));
    }
}

if (!function_exists('encodeId')) {
    /**
     * Encode id
     *
     * @param $id
     *
     * @return string
     */
    function encodeId($id)
    {
        return md5("MyTh").base64_encode($id).md5("Ahmed");
    }
}

if (!function_exists('decodeId')) {
    /**
     * Encode id
     *
     * @param $encode
     *
     * @return false|string
     */
    function decodeId($encode)
    {
        $first = md5("MyTh");
        $last = md5("Ahmed");
        $id = str_ireplace($first, '', $encode);
        $id = str_ireplace($last, '', $id);
        return base64_decode($id);
    }
}

if (!function_exists('downloadMedia')) {
    /**
     * Get Route url for media library
     *
     * @param  \Spatie\MediaLibrary\MediaCollections\Models\Media|\Illuminate\Database\Eloquent\Model|string|int  $media
     * @param  string  $routeName
     *
     * @return string
     * @uses route
     * @uses \Illuminate\Database\Eloquent\Model
     * @uses \Spatie\MediaLibrary\MediaCollections\Models\Media
     */
    function downloadMedia($media, string $routeName = 'Static.downloadMedia')
    {
        $id = $media instanceof Model ? $media->id : $media;
        return $media ? route($routeName, encodeId($id)) : null;
    }
}

if (!function_exists('front_end_url')) {
    /**
     *  Front end url helper
     *
     * @param  string|null  $prefix
     *
     * @return string
     */
    function front_end_url(string $prefix = null)
    {
        $url = config('4myth-tools.front_end_url', '');
        $url = rtrim($url, '/');
        if ($prefix) {
            $url .= "/".ltrim($prefix, '/');
        }
        return $url;
    }
}

if (!function_exists('getPermissionRoutes')) {
    /**
     * Get list of auth-routes has permission
     *
     * @param  false  $asCode
     *
     * @return array|null
     */
    function getPermissionRoutes(bool $asCode = false)
    {
        /** @var \Illuminate\Routing\Route[] $routes */
        $routes = Router::getRoutes();
        $auth = [];
        $codes = [];
        foreach ($routes as $route) {
            $name = $route->getName() ?: '';
            $code = $name;
            if (Str::endsWith($name, config('4myth-tools.skip_permission_ends_with', []))) {
                continue;
            }
            $action = $route->getAction();
            $middlewares = ($action['middleware'] ?? []);
            if (!in_array('permission', $middlewares)) {
                continue;
            }

            $hasAuth = !1;
            foreach ($middlewares as $middleware) {
                if (Str::contains($middleware, 'auth:')) {
                    $hasAuth = !0;
                    break;
                }
            }
            if (!$hasAuth) {
                continue;
            }

            // $generalRouteName = Str::afterLast($name, '.');
            //d($action, $generalRouteName,get_class($route),$route->getName());
            //d($generalRouteName);
            //d($code, $name);
            // if(in_array($generalRouteName, $only)){
            //     $code = PermissionMiddleware::routeCode($name, $generalRouteName);
            //     $name = PermissionMiddleware::routeName($name, $generalRouteName);
            // }

            if (!array_key_exists($code, $auth)) {
                $auth[$code] = [
                    'name' => $name,
                    'code' => $code,
                ];
                $codes[] = $code;
            }
        }
        ksort($auth);

        return $asCode ? $codes : $auth;
    }
}

if (!function_exists('apiResource')) {
    /**
     * Helper to make application auth-routes
     *
     * @param  string  $name
     * @param  string|string[]  $controller
     * @param  \Closure|null  $group
     * @param  array  $routeOptions
     *
     * @return \Illuminate\Routing\PendingResourceRegistration
     */
    function apiResource(string $name, array|string $controller, Closure $group = null, array $routeOptions = []): PendingResourceRegistration
    {
        Router::group(['as' => "$name.", 'prefix' => $name], function ($router) use ($name, $controller, $group) {
            if (is_callable($group)) {
                $group($router);
            }
            //Route::delete('destroy-all', [$controller, 'destroyAll'])->name('destroyAll');
        });
        return Router::apiResource($name, $controller, $routeOptions);
    }
}

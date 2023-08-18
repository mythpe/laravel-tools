<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

use GeniusTS\HijriDate\Date;
use GeniusTS\HijriDate\Hijri;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Routing\PendingResourceRegistration;
use Illuminate\Routing\Route;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route as Router;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

if (!function_exists('mythAllowHeaders')) {
    /**
     * @param int $code
     *
     * @return void
     */
    function mythAllowHeaders(int $code = 600): void
    {
        if (!app()->runningInConsole() || !app()->runningUnitTests()) {
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: *');
            header('Access-Control-Allow-Headers: *');
            http_response_code($code);
        }
    }
}

if (!function_exists('to_number_format')) {
    /**
     * @param float|int|string $number
     * @param int $decimals
     * @param string|null $currency
     * @param string $thousands_sep
     * @param string $dec_point
     *
     * @return string
     */
    function to_number_format(float | int | string $number, int $decimals = 2, ?string $currency = null, string $thousands_sep = ',', string $dec_point = '.'): string
    {
        $v = number_format((float) $number, $decimals, $dec_point, $thousands_sep);
        return $v.($currency ? " $currency" : '');
    }
}

if (!function_exists('ends_with')) {
    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string $haystack
     * @param array|string $needles
     *
     * @return bool
     */
    function ends_with(string $haystack, array | string $needles): bool
    {
        return Str::endsWith($haystack, $needles);
    }
}

if (!function_exists('starts_with')) {
    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string $haystack
     * @param array|string $needles
     *
     * @return bool
     */
    function starts_with(string $haystack, array | string $needles): bool
    {
        return Str::startsWith($haystack, $needles);
    }
}

if (!function_exists('d')) {
    /**
     * @param mixed ...$args
     *
     * @deprecated move to dd laravel helper
     */
    function d(...$args): void
    {
        if (!app()->runningInConsole() && !app()->runningUnitTests()) {
            mythAllowHeaders();
        }
        //$debug = @debug_backtrace();
        //$call = current($debug);
        //$line = ($call['line'] ?? __LINE__);
        //$file = ($call['file'] ?? __FILE__);
        //echo("[$file] Line ($line): <br>");
        //foreach ($args as $v) {
        //    VarDumper::dump($v);
        //}
        //die(1);
        call_user_func_array('dd', $args);
    }
}

if (!function_exists('locale_attribute')) {
    /**
     * Get attribute by locale
     *
     * @param string $attribute
     * @param string|null $locale
     *
     * @return string
     * @uses app()->getLocale()
     */
    function locale_attribute(string $attribute = "name", string $locale = null): string
    {
        is_null($locale) && ($locale = app()->getLocale());
        return rtrim($attribute, '_')."_".$locale;
    }
}

if (!function_exists('str_replace_en_ar')) {
    /**
     * Replace string for AR & EN
     *
     * @param string|null $string $string
     *
     * @return string
     */
    function str_replace_en_ar(?string $string = ''): string
    {
        return str_replace_name_ar(str_replace_name_en($string ?: ''));
    }
}

if (!function_exists('str_replace_name_ar')) {
    /**
     * Replace string for AR Name
     *
     * @param string|null $string $string
     *
     * @return string
     */
    function str_replace_name_ar(?string $string = ''): string
    {
        $string ??= '';
        $string = str_ireplace(['إ', 'أ'], 'ا', $string);
        $string = str_ireplace("عبدال", 'عبد ال', $string);
        return trim($string);
    }
}

if (!function_exists('str_replace_name_en')) {
    /**
     * Replace string for EN Name
     *
     * @param string|null $string $string
     *
     * @return string
     */
    function str_replace_name_en(?string $string = ''): string
    {
        $string ??= '';
        $string = trim($string);
        return ucwords($string);
    }
}

if (!function_exists('date_by_locale')) {
    /**
     * Convert date By locale
     *
     * @param string|null $date
     * @param null $toLocale
     *
     * @return string
     */
    function date_by_locale(?string $date, $toLocale = null): string
    {
        if (!$date) {
            return '';
        }
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
            "قبل",
            "قبل",
            "قبل",
            "قبل",
            "بعد",
            "بعد",
            "دقائق",
            "دقائق",
            "دقيقة",
            "دقيقة",
            "د",
            "د",
            "ساعات",
            "ساعات",
            "ساعة",
            "ساعة",
            "أسابيع",
            "أسابيع",
            "أسبوع",
            "أسبوع",
            "أشهر",
            "أشهر",
            "شهر",
            "شهر",
            "سنوات",
            "سنوات",
            "سنة",
            "سنة",
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
            "ago",
            "Ago",
            "before",
            "Before",
            "after",
            "After",
            "minutes",
            "Minutes",
            "minute",
            "Minute",
            "min",
            "Min",
            "hours",
            "Hours",
            "hour",
            "Hour",
            "weeks",
            "Weeks",
            "week",
            "Week",
            "months",
            "Months",
            "month",
            "Month",
            "years",
            "Years",
            "year",
            "Year",
        ];

        try {
            $str = $date;
            $str = str_ireplace([
                "seconds",
                "Seconds",
                "second",
                "Second",
            ], 'sec', $str);
            $str = str_ireplace([
                "minutes",
                "Minutes",
                "minute",
                "Minute",
            ], 'min', $str);
            $str = str_ireplace([
                "before",
                "Before",
            ], 'ago', $str);
            $str = str_ireplace($toLocale === 'ar' ? $notAr : $ar, $toLocale === 'ar' ? $ar : $notAr, $str);
            $str = str_ireplace([
                "ساعة",
                "ساعات",
            ], [
                '1 س',
                'س',
            ], $str);
            //$str = str_ireplace([
            //    "ثانية",
            //    //"ثواني",
            //    //"ثوان",
            //], 'ث', $str);
            return str_ireplace([
                "دقيقة",
                "دقائق",
            ], 'د', $str);
        }
        catch (Exception $exception) {
            return '';
        }
    }
}

if (!function_exists('manifest_directory')) {
    function manifest_directory($path = null): string
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
     * @param string $key
     * @param string|null $locale
     * @param bool $fallback
     *
     * @return bool
     */
    function trans_has(string $key, ?string $locale = null, bool $fallback = !1): bool
    {
        return app('translator')->has($key, $locale, $fallback);
    }
}

if (!function_exists('hijri')) {
    /**
     * helper convert to hijri
     *
     * @param mixed|string $date
     *
     * @return Date
     */
    function hijri($date = ''): Date
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
     * @param bool|string|null $append
     *
     * @return string
     */
    function arabic_date($string, bool | string $append = null): string
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
    function isBase64($str): bool
    {
        return str_contains($str, ';base64') || base64_encode(base64_decode($str, true)) === $str;
    }
}

if (!function_exists('appName')) {
    /**
     * Setting app name
     *
     * @param string|null $locale
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
    function encodeId($id): string
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
     * @return bool|string
     */
    function decodeId($encode): bool | string
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
     * @param Media|Model|string|int $media
     * @param string $routeName
     *
     * @return string|null
     * @uses route
     * @uses Model
     * @uses Media
     */
    function downloadMedia($media, string $routeName = 'Static.downloadMedia'): ?string
    {
        $id = $media instanceof Model ? $media->id : $media;
        return $media ? route($routeName, encodeId($id)) : null;
    }
}

if (!function_exists('front_end_url')) {
    /**
     *  Front end url helper
     *
     * @param string|null $prefix
     *
     * @return string
     */
    function front_end_url(?string $prefix = null): string
    {
        $url = config('4myth-tools.front_end_url', '');
        $url = rtrim($url, '/');
        if ($prefix) {
            $url .= "/".ltrim($prefix, '/');
        }
        return $url;
    }
}

if (!function_exists('getRouterPermissions')) {
    /**
     * Get list of auth-routes has permission
     *
     * @param false $asCode
     *
     * @return array
     */
    function getRouterPermissions(bool $asCode = false): array
    {
        /** @var Route[] $routes */
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
     * @param string $name
     * @param string|string[] $controller
     * @param Closure|null $group
     * @param array $routeOptions
     *
     * @return PendingResourceRegistration
     */
    function apiResource(string $name, array | string $controller, Closure $group = null, array $routeOptions = []): PendingResourceRegistration
    {
        Router::group(['as' => "$name.", 'prefix' => $name], function ($router) use ($name, $controller, $group) {
            if (is_callable($group)) {
                $group($router);
            }
            //$router->delete('DestroyAll', [$controller, 'destroyAll'])->name('destroyAll');
            //$router->post('Export', [$controller, 'index'])->name('export');
        });
        return Router::apiResource($name, $controller, $routeOptions);
    }
}

if (!function_exists('isKsaMobile')) {
    /**
     * Verify the mobile number if it is a valid Saudi mobile number
     *
     * @param string|int $mobile
     *
     * @return bool
     */
    function isKsaMobile($mobile): bool
    {
        if ($mobile) {
            $mobile = (int) $mobile;
            return Str::startsWith($mobile, 966) && strlen($mobile) == 12 || Str::startsWith($mobile, 5) && strlen($mobile) == 9;
        }
        return !1;
    }
}

if (!function_exists('developmentMode')) {
    /**
     * Check if environment in development mode
     *
     * @return bool
     */
    function developmentMode(): bool
    {
        return app()->environment(config('4myth-tools.development_modes', []));
    }
}

if (!function_exists('fixString')) {
    /**
     * trim & translate string
     *
     * @param $string
     * @param string|null $translate translate string target
     * @param bool $ucwords use PHP functions ucwords
     * @param array|null $replace array to use in str_ireplace
     *
     * @return string $string
     */
    function fixString($string, string | null $translate = null, $ucwords = !0, array | null $replace = null): string
    {
        $string = str_replace_en_ar($string);
        is_array($replace) && ($string = trim(str_ireplace($replace[0], $replace[1], $string)));
        if ($translate) {
            $string = Str::slug($string, ' ', $translate);
        }
        if ($ucwords) {
            $string = ucwords($string);
        }
        return trim($string) ?? '';
    }
}

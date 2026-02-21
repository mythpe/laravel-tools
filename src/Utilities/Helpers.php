<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Intl\Countries;

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

    /**
     * write file
     * @param $path
     * @param array|Collection $contents
     * @param array $options
     * @return void
     */
    public static function writeFile($path, array | Collection $contents, array $options = []): void
    {
        $disk = Storage::disk('root');
        $isPhp = ($options['php'] ?? Str::endsWith($path, '.php'));
        $isDirectories = ($options['directories'] ?? !1);
        $directories = $isDirectories ? $contents : [$contents];
        $storePath = ($options['output'] ?? 'resources/setup/deploy');
        $year = now()->format('Y');
        $ext = $isPhp ? 'php' : 'json';

        foreach ($directories as $dirKey => $dirValue) {
            $content = '';
            $exportPath = Str::finish($storePath.($dirKey ? "/$dirKey" : '')."/$path", ".$ext");
            $values = collect();
            foreach ($dirValue as $k => $v) {
                if ($isPhp) {
                    $values->push("\t'$k' => '$v'");
                }
            }

            if ($isPhp) {
                $content = "<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-$year All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

return [
{$values->implode(','."\n")},
];
";
            }
            $disk->put($exportPath, $content);
            if (($c = ($options['callback'] ?? null)) && is_callable($c)) {
                $c($disk->path($exportPath));
            }
        }
    }

    /**
     * Check if model has date cast
     * @param Model $model
     * @param string $key
     * @return bool
     */
    public static function hasDateCast(Model $model, string $key): bool
    {
        if ($model::CREATED_AT == $key || $model::UPDATED_AT == $key) {
            return !0;
        }
        if (method_exists($model, 'getDeletedAtColumn') && $model->getDeletedAtColumn() == $key) {
            return !0;
        }
        return $model->hasCast($key, [
            'date',
            'datetime',
            'custom_datetime',
            'immutable_date',
            'immutable_custom_datetime',
            'immutable_datetime',
            'timestamp',
        ]);
    }

    /**
     * Check if model has numeric cast
     * @param Model $model
     * @param string $key
     * @return bool
     */
    public static function hasNumericCast(Model $model, string $key): bool
    {
        return $model->hasCast($key, ['int', 'integer', 'real', 'float', 'double', 'decimal']);
    }

    public static function columnBeforeLast($column, $last = [])
    {
        $last = array_unique(array_merge($last, [
            '_to_string',
            'ToString',
            '_to_yes',
            'ToYes',
            '_to_number_format',
            'toNumberFormat',
            '_to_date_format',
            'toDateFormat',
            '_to_datetime_format',
            'toDateTimeFormat',
            'toDatetimeFormat',
        ]));
        foreach ($last as $c) {
            if (Str::endsWith($column, $c)) {
                return Str::beforeLast($column, $c);
            }
        }
        return $column;
    }

    public static function getDistance(?array $coordinateFrom = null, ?array $coordinateTo = null, int $earthRadius = 6371, $precision = 2): float | null
    {
        if (!$coordinateFrom || !$coordinateTo) {
            return null;
        }
        $latitudeFrom = $coordinateFrom['latitude'] ?? null;
        $longitudeFrom = $coordinateFrom['longitude'] ?? null;

        $latitudeTo = $coordinateTo['latitude'] ?? null;
        $longitudeTo = $coordinateTo['longitude'] ?? null;
        if (!$latitudeFrom || !$longitudeFrom || !$latitudeTo || !$longitudeTo) {
            return null;
        }
        // convert from degrees to radians
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);
        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;
        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) + cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));
        return round($angle * $earthRadius, $precision);
    }

    /**
     * @param $value
     * @param string $separator
     * @param bool $int
     * @param bool $unique
     * @return array
     */
    public static function splitString($value, string $separator = ',;|', bool $int = !1, bool $unique = !0): array
    {
        if (empty($value)) {
            return [];
        }

        if (!is_array($value)) {
            $value = collect(preg_split('/\s*['.$separator.']\s*/', $value, -1, PREG_SPLIT_NO_EMPTY))
                ->when($int, fn($collect) => $collect->filter(fn($item) => is_numeric(trim($item))))
                ->when($int, fn($collect) => $collect->map(fn($item) => (int) (trim($item))))
                ->when($unique, fn($collect) => $collect->unique())
                ->values()
                ->toArray();
        }
        return $value;
    }

    /**
     * @param $text
     * @return bool
     */
    public static function isHtmlEmpty($text): bool
    {
        if (empty($text)) {
            return true;
        }
        $text = strip_tags($text);
        $text = str_replace('&nbsp;', '', $text);
        $text = preg_replace('/[^\S ]+/', ' ', $text);
        $text = preg_replace('/\s+/', ' ', $text);
        return empty(trim($text));
    }

    /**
     * Parse a template string and replace placeholders with values from the data array
     * @param string $string The template string to parse
     * @param array<string,\Closure|string> $data Associative array of placeholder values
     * @param mixed|null $context Additional context to pass to callable values (optional)
     * @param \Closure|string|null $default Default value for missing placeholders (optional)
     * @param string $pattern Regex pattern to find placeholders (default: '/\{(\w+)\}/')
     * @return string The parsed template with replaced values
     *
     * @example
     * $template = "Hello {name}, you have {count} messages";
     * $data = ['name' => 'Ahmed', 'count' => 5];
     * Output: "Hello Ahmed, you have 5 messages"
     */
    public static function parseTemplate(
        string $string,
        array  $data = [],
        mixed  $context = null,
        mixed  $default = null,
        string $pattern = '/\{(\w+)\}/',
    ): string
    {
        return preg_replace_callback($pattern, function ($matches) use (&$data, &$context, $default) {
            $key = $matches[1] ?? null;
            if ($key) {
                $value = $data[$key] ?? $matches[0] ?? '';
                return is_callable($value) ? $value($context) : $value;
            }
            if (null !== $default) {
                return is_callable($default) ? $default($context) : $default;
            }
            return $matches[0] ?? '';
        }, $string);
    }

    /**
     * @param  array|null  $codes
     * @return array
     */
    public static function countries(?array $codes = ['SA']): array
    {
        $phoneUtil = PhoneNumberUtil::getInstance();
        if (empty($codes)) {
            $codes = $phoneUtil->getSupportedRegions();
        }
        $countries = [];
        $arNames = Countries::getNames('ar');
        $enNames = Countries::getNames('en');
        foreach ($codes as $countryCode) {
            $countryCallingCode = $phoneUtil->getCountryCodeForRegion($countryCode);
            $ar = $arNames[$countryCode] ?? null;
            $en = $enNames[$countryCode] ?? null;
            $id = $countryCode;
            $key = "$countryCallingCode";
            $countries[] = [
                'id'         => $id,
                'value'      => $id,
                'label'      => "$key • $id",
                'code'       => $id,
                'code_label' => $id,
                'name_ar'    => $ar,
                'name_en'    => $en,
                'key'        => $key,
                'flag'       => static::countryCodeToFlag($countryCode),
            ];
        }
        usort($countries, function ($a, $b) {
            return strcmp($a['key'], $b['key']);
        });
        return $countries;
    }

    public static function countryCodeToFlag($countryCode): string
    {
        $countryCode = strtoupper($countryCode);
        if (strlen($countryCode) !== 2 || !ctype_alpha($countryCode)) {
            return '';
        }

        $flag = '';
        foreach (str_split($countryCode) as $letter) {
            $flag .= mb_chr(127397 + ord($letter));
        }

        return $flag;
    }
}

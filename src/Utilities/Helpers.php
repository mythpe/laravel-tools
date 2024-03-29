<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
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
{$values->implode(','.PHP_EOL)}
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
        return $model->hasCast($key, ['date', 'datetime', 'custom_datetime', 'immutable_date', 'immutable_custom_datetime', 'immutable_datetime', 'timestamp']);
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
}
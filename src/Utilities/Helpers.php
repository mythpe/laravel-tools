<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Utilities;

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
        $storePath = ($options['export'] ?? '/resources/setup/deploy/exported');
        if ($isPhp) {
            $directories = $isDirectories ? $contents : [$contents];
            foreach ($directories as $dirKey => $dirValue) {
                $exportPath = Str::finish("$storePath".($dirKey ? "/$dirKey" : '')."/{$path}", '.php');
                $values = collect();
                foreach ($dirValue as $k => $v) {
                    $values->push("'$k' => '$v'");
                }
                $php = <<<php
<?php

return [
\r{$values->implode(','.PHP_EOL)}
];
php;

                $disk->put($exportPath,
                $php);
                if (($c = ($options['callback'] ?? null)) && is_callable($c)) {
                    $c($disk->path($exportPath));
                }
            }
        }
    }
}
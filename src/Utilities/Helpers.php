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
        $directories = $isDirectories ? $contents : [$contents];
        $storePath = ($options['output'] ?? resource_path('setup/deploy'));
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
}
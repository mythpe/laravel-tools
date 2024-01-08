<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Commands\Export;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Myth\LaravelTools\Console\BaseCommand;
use Myth\LaravelTools\Controllers\Controller;
use Myth\LaravelTools\Models\BaseModel;
use Myth\LaravelTools\Utilities\Helpers;
use ReflectionClass;

class ExportAttributesCommand extends BaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:export-attributes
{--o|output= : Output path inside resource path}
{--t|to : Do not Insert to_ keys to exported data}
{--f|from : Do not  Insert from_ keys to exported data}
{--w|with : Do not  Export attributes with exists files}
{--d|deploy : use Language Files Command }
{--e|export : Export file to lang}
';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Export attributes & constants of model';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $appDisk = Storage::disk('app');
        $langDisk = Storage::disk('lang');
        $modelsFiles = $appDisk->allFiles('Models');
        $controllersFiles = $appDisk->allFiles('Http/Controllers');
        $attributes = [];
        $choice = [];
        $additionalChoice = [];
        $locales = $langDisk->allDirectories();
        $toOption = !$this->option('to');
        $fromOption = !$this->option('from');
        $withOption = !$this->option('with');
        $exportOption = $this->option('export');
        $deployOption = $this->option('deploy');

        $cacheAttrs = [
            'ar' => require __DIR__.'/../../../lang/ar/attributes.php',
            'en' => require __DIR__.'/../../../lang/en/attributes.php',
        ];
        $cacheChoice = [
            'ar' => require __DIR__.'/../../../lang/ar/choice.php',
            'en' => require __DIR__.'/../../../lang/en/choice.php',
        ];

        foreach ($locales as $locale) {
            $l = pathinfo($locale, PATHINFO_FILENAME);
            $attributes[$l] = [];
            $choice[$l] = [];
        }
        $modelsFiles = collect($modelsFiles)->filter(fn($name) => !Str::contains($name, Str::afterLast(BaseModel::class, '\\'))
            && !Str::contains($name, ['/Pivots/'])
        );
        // d($modelsFiles);
        foreach ($modelsFiles as $file) {
            // if (Str::contains($file, Str::afterLast(BaseModel::class, '\\'))) {
            //     continue;
            // }
            $c = Str::beforeLast($file, '.php');
            $c = str_replace(['/', '\\\\'], '\\', $c);
            $namespace = "\App\\{$c}";
            if (!class_exists($namespace)) {
                continue;
            }
            /** @var BaseModel $model */
            $model = app($namespace);
            $fillable = [];

            if (method_exists($model, 'getFillable')) {
                $fillable = array_unique(array_merge($fillable, $model->getFillable()));
            }

            if (method_exists($model, 'getAppends')) {
                $fillable = array_unique(array_merge($fillable, $model->getAppends()));
            }

            if (method_exists($model, 'getHidden')) {
                $fillable = array_unique(array_merge($fillable, $model->getHidden()));
            }

            if (method_exists($model, 'getTable')) {
                $fillable = array_unique(array_merge($fillable, Schema::getColumnListing($model->getTable())));
            }

            $fillable = array_unique(array_merge($fillable, config('4myth-tools.export_attributes')));
            $parents = explode('\\', $model::class);

            if (count($parents) > 3) {
                unset($parents[count($parents) - 1]);
                unset($parents[0]);
                unset($parents[1]);
                foreach ($parents as $v) {
                    $additionalChoice[] = $v;
                }
            }

            // d($controllersFiles);

            foreach ($controllersFiles as $controller) {
                $fileName = 'App\\'.Str::before(str_replace('/', '\\', $controller), '.php');
                if (!class_exists($fileName)) {
                    continue;
                }
                $c = app($fileName);
                if (!$c instanceof Controller) {
                    continue;
                }
                $r = new ReflectionClass($c);
                foreach ($r->getMethods() as $method) {
                    $methodName = $method->getName();
                    if (starts_with($methodName, '_') && $method->getReturnType() == 'array') {
                        $fillable = array_unique(array_merge($fillable, array_keys($c->{$methodName}())));
                    }
                }
            }
            $fillable = array_filter($fillable, fn($value) => !is_numeric($value));
            $class_basename = class_basename($model);
            $classSnake = Str::snake($class_basename);
            $classCamel = Str::camel($class_basename);
            $classPascal = ucfirst($classCamel);
            $fillable[] = "{$classSnake}_id";
            $fillable[] = Str::plural($classSnake)."_id";

            // Customizing
            if ($class_basename == 'Setting' && method_exists($model, 'getAll')) {
                $fillable = array_merge($fillable, array_keys($model::getAll()));
            }

            $class_reflex = new ReflectionClass($model);
            $class_constants = $class_reflex->getConstants();
            foreach ($class_constants as $constant) {
                if (is_string($constant)) {
                    $fillable[] = $constant;
                }
            }

            $sortArray = [];
            foreach ($fillable as $value) {
                if ($value != 'id' && !ends_with($value, '_id')) {
                    if ($fromOption && (Helpers::hasDateCast($model, $value) || Helpers::hasNumericCast($model, $value))) {
                        $fillable[] = "from_$value";
                    }
                    if ($toOption && (Helpers::hasDateCast($model, $value) || Helpers::hasNumericCast($model, $value))) {
                        $fillable[] = "to_$value";
                    }
                }
                // insert attributes by locale. 'name_{locale}'
                foreach ($locales as $locale) {
                    if (ends_with($value, ($last = "_$locale"))) {
                        $fillable[] = Str::beforeLast($value, $last);
                        break;
                    }
                }
            }
            $fillable = collect($fillable)->unique()->filter(fn($v) => !Str::contains($v, ['pivot_', '_pivot', '_pivot_']))->values()->toArray();
            sort($fillable);
            $temp = [];
            foreach ($fillable as $k => $value) {
                $hasFrom = starts_with($value, 'from_');
                $hasTo = starts_with($value, 'to_');
                $strBeforeToFrom = Str::after($value, '_');
                // Sort
                if (($hasFrom || $hasTo) && in_array($strBeforeToFrom, $fillable)) {
                    $attributeKey = "{$strBeforeToFrom}_{$value}";
                    $sortArray[$attributeKey] = $value;
                    $temp[$k] = $attributeKey;
                }
                else {
                    $temp[$k] = $value;
                }
            }
            $fillable = $temp;
            sort($fillable);
            // dd($fillable);

            foreach ($locales as $locale) {
                foreach ($fillable as $attribute) {
                    if (isset($sortArray[$attribute])) {
                        $attribute = $sortArray[$attribute];
                    }
                    $transKey = "attributes.$attribute";
                    $transHas = trans_has($transKey, $locale);
                    $defualtTrans = strlen($attribute) > 2 ? ucfirst(str_replace('_', ' ', ucwords(Str::snake(ends_with($attribute, '_id') ? Str::beforeLast($attribute, '_id') : $attribute), '_'))) : strtoupper($attribute);
                    $transValue = $defualtTrans;
                    if ($transHas) {
                        $transValue = __($transKey, [], $locale);
                    }
                    elseif (isset($cacheAttrs[$locale][$attribute])) {
                        $transValue = $cacheAttrs[$locale][$attribute];
                    }
                    $hasFrom = starts_with($attribute, 'from_');
                    $hasTo = starts_with($attribute, 'to_');
                    $strBeforeToFrom = Str::after($attribute, '_');
                    if ($hasFrom || $hasTo) {
                        if (trans_has($t = "attributes.$strBeforeToFrom", $locale) && !Str::contains($transValue, $v = __($t, [], $locale))) {
                            if ($locale == 'ar') {
                                $transValue = sprintf($v.' %s', $hasFrom ? 'من' : ($hasTo ? 'إلى' : ''));
                            }
                            else {
                                $transValue = sprintf('%s '.$v, $hasFrom ? 'From' : ($hasTo ? 'To' : ''));
                            }
                        }
                        elseif (isset($cacheAttrs[$locale][$attribute])) {
                            $transValue = $cacheAttrs[$locale][$attribute];
                        }
                    }
                    // No value set from cache
                    if ($transValue == $defualtTrans && isset($cacheAttrs[$locale][$attribute])) {
                        $transValue = $cacheAttrs[$locale][$attribute];
                    }

                    $attributes[$locale][$attribute] = $transValue;
                }

                if (!empty($sortArray)) {
                    $temp = [];
                    foreach ($attributes[$locale] as $k => $v) {
                        if (isset($sortArray[$k])) {
                            $temp[$sortArray[$k]] = $v;
                        }
                        else {
                            $temp[$k] = $v;
                        }
                    }
                    $attributes[$locale] = $temp;
                }

                if ($withOption) {
                    $localeFile = include lang_path("$locale/attributes.php");
                    $withFillable = array_keys($localeFile);
                    $attributes[$locale] = array_merge($attributes[$locale], $localeFile);
                }
            }
            $key = Str::plural($classPascal);
            $k = "choice.$key";
            foreach ($locales as $locale) {
                if (Str::contains($key, 'Pivot')) {
                    continue;
                }

                foreach ($additionalChoice as $v) {
                    if (trans_has($i = "choice.$v", $locale)) {
                        if (isset($cacheChoice[$locale][$v])) {
                            $choice[$locale][$v] = $cacheChoice[$locale][$v];
                            continue;
                        }
                        $choice[$locale][$v] = __($i, [], $locale);
                        continue;
                    }

                    $plural = str_replace('-', ' ', Str::plural(ucwords(Str::kebab($v), '-')));
                    $singular = str_replace('-', ' ', Str::singular(ucwords(Str::kebab($v), '-')));
                    if ($locale == 'ar') {
                        $choice[$locale][$v] = "$plural|$singular";
                    }
                    else {
                        $choice[$locale][$v] = "$singular|$plural";
                    }
                }

                if (isset($cacheChoice[$locale])) {
                    $choice[$locale] = array_merge($cacheChoice[$locale], $choice[$locale]);
                }

                if (isset($cacheChoice[$locale][$key])) {
                    $choice[$locale][$key] = $cacheChoice[$locale][$key];
                }
                if (!isset($choice[$locale][$key])) {
                    $choice[$locale][$key] = null;
                }
                // if (__($k, [], $locale) != $k) {
                if (!$choice[$locale][$key] && trans_has($k, $locale)) {
                    $choice[$locale][$key] = __($k, [], $locale);
                }
                else {
                    $plural = str_replace('-', ' ', Str::plural(ucwords(Str::kebab($class_basename), '-')));
                    $singular = str_replace('-', ' ', Str::singular(ucwords(Str::kebab($class_basename), '-')));
                    if ($locale == 'ar') {
                        $choice[$locale][$key] = "$plural|$singular";
                    }
                    else {
                        $choice[$locale][$key] = "$singular|$plural";
                    }
                }
                $choiceLang = is_file($p = lang_path("$locale/choice.php")) ? include $p : [];
                $choice[$locale] = array_merge($choice[$locale], $choiceLang);
                ksort($choice[$locale]);
            }
        }
        $outputPath = $this->option('output') ?: 'resources/setup/deploy';
        $callback = function ($exportedPath) use ($outputPath, $exportOption) {
            if ($exportOption) {
                $from = trim(str_ireplace(base_path(), '', $exportedPath), '/\\');
                $to = trim(str_ireplace(base_path(), '', $exportedPath), '/\\');
                $to = lang_path(trim(str_ireplace($outputPath, '', $to), '/\\'));
                $to = trim(str_ireplace(base_path(), '', $to), '/\\');
                $disk = Storage::disk('root');
                $disk->copy($from, $to);
                $to = str_ireplace('/', '\\', $to);
                $this->components->info("Copy File [$to]");
            }
            $exportedPath = trim(str_ireplace(base_path(), '', $exportedPath), '/\\');
            $exportedPath = trim(str_ireplace('/', '\\', $exportedPath), '/\\');
            $this->components->info("Export file [$exportedPath]");
        };
        Helpers::writeFile("attributes.php", $attributes, [
            'output'      => $outputPath,
            'directories' => !0,
            'callback'    => $callback,
        ]);
        Helpers::writeFile("choice.php", $choice, [
            'output'      => $outputPath,
            'directories' => !0,
            'callback'    => $callback,
        ]);

        if ($deployOption) {
            $this->call('myth:export-lang');
        }
    }
}

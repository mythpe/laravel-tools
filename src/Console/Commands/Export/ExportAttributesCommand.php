<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Commands\Export;

use Illuminate\Support\Collection;
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
    protected $signature = 'myth:attrs
{--o|output= : Output path inside resource path}
{--t|to : Do not Insert to_ keys to exported data}
{--f|from : Do not  Insert from_ keys to exported data}
{--N|new : Make new export and do not export attributes with exists files}
{--D|delete : Delete exported files}
{--j|json : Use Language Files Command }
{--s|save : save files to lang directories}
{--c|choice : with exists choice}
{--C|countables : with exists countables}
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
        $toOption = $this->option('to');
        $fromOption = $this->option('from');
        $newOption = $this->option('new');
        $withChoiceOption = $this->option('choice');
        $withCountablesOption = $this->option('countables');
        $saveOption = $this->option('save');
        $jsonOption = $this->option('json');

        $cacheAttrs = [
            'ar' => require __DIR__.'/../../../lang/ar/attributes.php',
            'en' => require __DIR__.'/../../../lang/en/attributes.php',
        ];
        $cacheChoice = [
            'ar' => require __DIR__.'/../../../lang/ar/choice.php',
            'en' => require __DIR__.'/../../../lang/en/choice.php',
        ];
        $cacheCountables = [
            'ar' => require __DIR__.'/../../../lang/ar/countables.php',
            'en' => require __DIR__.'/../../../lang/en/countables.php',
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
            /** @var Collection $fillable */
            $fillable = collect([]);

            if (method_exists($model, 'getFillable')) {
                $fillable = $fillable->merge($model->getFillable());
            }

            if (method_exists($model, 'getAppends')) {
                $fillable = $fillable->merge($model->getAppends());
            }

            if (method_exists($model, 'getHidden')) {
                $fillable = $fillable->merge($model->getHidden());
            }

            if (method_exists($model, 'getTable')) {
                $fillable = $fillable->merge(Schema::getColumnListing($model->getTable()));
            }
            $fillable = $fillable->merge(config('4myth-tools.export_attributes', []));
            $parents = explode('\\', $model::class);
            if (count($parents) > 3) {
                unset($parents[count($parents) - 1]);
                unset($parents[0]);
                unset($parents[1]);
                foreach ($parents as $v) {
                    $additionalChoice[] = $v;
                }
            }
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
                    if (($methodName == 'getRules' || starts_with($methodName, '_')) && $method->getReturnType() == 'array') {
                        $fillable = $fillable->merge(array_keys($c->{$methodName}()));
                    }
                }
            }
            $fillable = $fillable->filter(fn($value) => !is_numeric($value));
            $class_basename = class_basename($model);
            $classSnake = Str::snake($class_basename);
            $classCamel = Str::camel($class_basename);
            $classPascal = ucfirst($classCamel);
            $fillable = $fillable->merge(["{$classSnake}_id", Str::plural($classSnake)."_id"]);

            // Customizing
            if ($class_basename == 'Setting' && method_exists($model, 'setting')) {
                $fillable = $fillable->merge(array_keys($model::setting()));
            }

            $class_reflex = new ReflectionClass($model);
            $class_constants = $class_reflex->getConstants();
            foreach ($class_constants as $constantKey => $constant) {
                if (Str::endsWith(strtolower($constantKey ?: ''), ['_status', '_const'])) {
                    $fillable = $fillable->filter(fn($v) => $v != $constant);
                    continue;
                }
                if (Str::startsWith(strtolower($constantKey ?: ''), ['hash_', 'const_'])) {
                    continue;
                }
                if (is_string($constant) && preg_match_all("/[\w\d]+/", $constant)) {
                    $fillable->push($constant);
                }
                elseif (is_array($constant)) {
                    $constantValues = array_values($constant);
                    if (empty($constantValues)) {
                        continue;
                    }
                    $numeric = array_filter($constantValues, fn($v) => !is_numeric($v));
                    if (empty($numeric)) {
                        continue;
                    }
                    $fillable = $fillable->merge($constantValues);
                }
            }

            $sortArray = [];
            $fillable = $fillable->filter()->unique()->values();
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
            $fillable = $fillable->filter(fn($v) => !Str::contains($v, [
                    'pivot_',
                    '_pivot',
                    '_pivot_',
                ]) && !Str::endsWith($v, '_to_string'))->values()->toArray();
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
            $fillable = collect($temp)->filter((fn($v) => !Str::endsWith('.*', $v)))->values()->toArray();
            sort($fillable);

            // # Set Attributes.
            foreach ($locales as $locale) {
                foreach ($fillable as $attribute) {
                    if (isset($sortArray[$attribute])) {
                        $attribute = $sortArray[$attribute];
                    }
                    $transKey = "attributes.$attribute";
                    $transHas = trans_has($transKey, $locale);
                    $defaultTrans = $this->defaultTranslate($attribute, $locale);
                    $transValue = $defaultTrans;
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
                        elseif (isset($cacheAttrs[$locale][$strBeforeToFrom])) {
                            $v = $cacheAttrs[$locale][$strBeforeToFrom];
                            if ($locale == 'ar') {
                                $transValue = sprintf($v.' %s', $hasFrom ? 'من' : ($hasTo ? 'إلى' : ''));
                            }
                            else {
                                $transValue = sprintf('%s '.$v, $hasFrom ? 'From' : ($hasTo ? 'To' : ''));
                            }
                        }
                    }
                    // # No value set from cache
                    if ($transValue == $defaultTrans && isset($cacheAttrs[$locale][$attribute])) {
                        $transValue = $cacheAttrs[$locale][$attribute];
                    }

                    $attributes[$locale][$attribute] = $transValue;
                }
                if (!$newOption) {
                    $localeFile = include lang_path("$locale/attributes.php");
                    $attributes[$locale] = [...$attributes[$locale], ...$localeFile];
                }
                // Sort Values.
                ksort($attributes[$locale]);

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

                if (isset($cacheChoice[$locale][$key])) {
                    $choice[$locale][$key] = $cacheChoice[$locale][$key];
                }
                if (!isset($choice[$locale][$key])) {
                    $choice[$locale][$key] = null;
                }

                if (!$choice[$locale][$key]) {
                    if (trans_has($k, $locale)) {
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
                }
                $localeChoice = is_file($p = lang_path("$locale/choice.php")) ? include $p : [];
                $choice[$locale] = [...$choice[$locale], ...$localeChoice];
                ksort($choice[$locale]);

                if ($withChoiceOption && isset($cacheChoice[$locale])) {
                    $choice[$locale] = [...$cacheChoice[$locale], ...$choice[$locale]];
                }

                if ($withCountablesOption && isset($cacheCountables[$locale])) {
                    $choice[$locale] = [...$cacheCountables[$locale], ...$choice[$locale]];
                }
            }
        }
        $outputPath = $this->option('output') ?: 'resources/setup/deploy';
        $callback = function ($exportedPath) use ($outputPath, $saveOption) {
            $disk = Storage::disk('root');
            if ($saveOption) {
                $from = trim(str_ireplace(base_path(), '', $exportedPath), '/\\');
                $to = trim(str_ireplace(base_path(), '', $exportedPath), '/\\');
                $to = lang_path(trim(str_ireplace($outputPath, '', $to), '/\\'));
                $to = trim(str_ireplace(base_path(), '', $to), '/\\');
                $disk->copy($from, $to);
                $to = str_ireplace('/', '\\', $to);
                $t = $this->option('delete') ? ' & Delete ' : ' ';
                $this->components->info("Copy{$t}File [$to]");
                if ($this->option('delete')) {
                    $disk->delete($from);
                }
            }
            else {
                $exportedPath = trim(str_ireplace(base_path(), '', $exportedPath), '/\\');
                $exportedPath = trim(str_ireplace('/', '\\', $exportedPath), '/\\');
                $this->components->info("Export file [$exportedPath]");
            }
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

        if ($jsonOption) {
            $this->call('myth:lang');
        }
    }

    public function defaultTranslate(string $attribute, string $locale): string
    {
        $key = $attribute;
        if (strtolower($attribute) == 'myth') {
            return 'MyTh';
        }
        if (strlen($attribute) == 3) {
            $attribute = strtoupper($attribute);
        }
        elseif (strlen($attribute) > 3) {
            $attribute = Str::of($attribute);
            if (Str::endsWith('_id', $attribute)) {
                $attribute = $attribute->beforeLast('_id');
            }
            $attribute = $attribute->snake()->title()->replace('_', ' ')->ucfirst();
            if ($attribute->contains('.*.')) {
                $attribute = Str::of($key);
                $attribute = $attribute->endsWith('.*.id') ? $attribute->before('.*.') : $attribute->afterLast('.*.');
                // dd($attribute);
                if (trans_has($tKey = "attributes.".$attribute->lower()->snake(), $locale, !0)) {
                    $attribute = __($tKey, [], $locale);
                }
            }
        }
        $last = substr($key, -3);
        if (in_array($last, ['_ar', '_en'])) {
            $attribute = substr($attribute, 0, -3);
            if ($locale == 'ar') {
                $attribute = "$attribute ".($last == '_en' ? "بالإنجليزية" : "بالعربية");
            }
            else {
                $attribute = ($last == '_en' ? "English" : "Arabic")." $attribute";
            }
        }
        return $attribute;
    }
}

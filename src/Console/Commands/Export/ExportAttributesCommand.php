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
    protected $signature = 'myth:export-attributes {--o|output= : Output path inside resource path}';

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
        $withChoice = [];
        $locales = $langDisk->allDirectories();
        foreach ($locales as $locale) {
            $l = pathinfo($locale, PATHINFO_FILENAME);
            $attributes[$l] = [];
            $choice[$l] = [];
        }

        foreach ($modelsFiles as $file) {
            if (Str::contains($file, Str::afterLast(BaseModel::class, '\\'))) {
                continue;
            }
            $c = Str::beforeLast($file, '.php');
            $c = str_replace(['/', '\\\\'], '\\', $c);
            /** @var BaseModel $model */
            $model = app("\App\\{$c}");
            $fillable = method_exists($model, 'getFillable') ? $model->getFillable() : [];

            if (method_exists($model, 'getAppends')) {
                $fillable = array_unique(array_merge($fillable, $model->getAppends()));
            }

            if (method_exists($model, 'getHidden')) {
                $fillable = array_unique(array_merge($fillable, $model->getHidden()));
            }

            if (method_exists($model, 'getModelTable')) {
                $fillable = array_unique(array_merge($fillable, Schema::getColumnListing($model::getModelTable())));
            }

            $fillable = array_unique(array_merge($fillable, config('4myth-tools.export_attributes')));
            $parents = explode('\\', $model::class);

            if (!Str::contains(strtolower($model::class), 'pivot') && count($parents) > 3) {
                unset($parents[count($parents) - 1]);
                unset($parents[0]);
                unset($parents[1]);
                foreach ($parents as $v) {
                    $withChoice[] = $v;
                }
            }

            foreach ($controllersFiles as $controller) {
                try {
                    $c = app('App\\'.Str::before(str_replace('/', '\\', $controller), '.php'));
                    if (!$c instanceof Controller) {
                        continue;
                    }
                    $r = new \ReflectionClass($c);
                    foreach ($r->getMethods() as $method) {
                        if (!Str::startsWith($method->getName(), '_') || $method->getReturnType() != 'array') {
                            continue;
                        }
                        $fillable = array_unique(array_merge($fillable, array_keys($c->{$method->getName()}())));
                    }
                }
                catch (\Exception $exception) {
                    // d($exception);
                }
            }

            $class_basename = class_basename($model);
            $classSnake = Str::snake($class_basename);
            $classCamel = Str::camel($class_basename);
            $classPascal = ucfirst($classCamel);
            $fillable[] = "{$classSnake}_id";
            $fillable[] = Str::plural($classSnake)."_id";

            // Customizing
            if ($class_basename == 'Setting' && method_exists($model, 'getAll')) {
                $fillable = array_merge($fillable, array_keys($model::getAll()));
                // d($fillable);
            }

            $class_reflex = new ReflectionClass($model);
            $class_constants = $class_reflex->getConstants();
            foreach ($class_constants as $constant) {
                if (is_string($constant)) {
                    $fillable[] = $constant;
                }
            }

            foreach ($fillable as $value) {
                if (method_exists($model, 'hasCast') && $value != 'id' && !Str::endsWith($value, '_id') && (Helpers::hasDateCast($model, $value) || Helpers::hasNumericCast($model, $value))) {
                    $fillable[] = "from_$value";
                    $fillable[] = "to_$value";
                }
                foreach ($locales as $locale) {
                    if (Str::endsWith($value, ($last = "_$locale"))) {
                        $fillable[] = Str::beforeLast($value, $last);
                        break;
                    }
                }
            }
            $fillable = collect($fillable)->filter(fn($v) => !Str::contains($v, ['pivot_', '_pivot', '_pivot_']))->values();
            $cashAttrs = [
                'ar' => require __DIR__.'/../../../lang/ar/attributes.php',
                'en' => require __DIR__.'/../../../lang/en/attributes.php',
            ];
            $cashChoice = [
                'ar' => require __DIR__.'/../../../lang/ar/choice.php',
                'en' => require __DIR__.'/../../../lang/en/choice.php',
            ];
            foreach ($fillable as $attribute) {
                foreach ($locales as $locale) {
                    if (isset($cashAttrs[$locale][$attribute])) {
                        $transValue = $cashAttrs[$locale][$attribute];
                    }
                    else {
                        $transKey = "attributes.{$attribute}";
                        $transHas = trans_has($transKey, $locale);
                        $transValue = __($transKey, [], $locale);
                        $hasFrom = Str::startsWith($attribute, 'from_');
                        $hasTo = Str::startsWith($attribute, 'to_');
                        $k = Str::after($attribute, '_');
                        if (($hasFrom || $hasTo) && !Str::contains($transValue, __("attributes.$k", [], $locale), !0)) {
                            if ($locale == 'ar') {
                                $transValue = sprintf(__("attributes.$k", [], $locale).' %s', $hasFrom ? 'من' : ($hasTo ? 'إلى' : ''));
                            }
                            else {
                                $transValue = sprintf('%s '.__("attributes.$k", [], $locale), $hasFrom ? 'From' : ($hasTo ? 'To' : ''));
                            }
                        }

                        if (!$transHas || $transValue == $transKey) {
                            $transValue = ucfirst(str_replace('_', ' ', ucwords(Str::snake(Str::endsWith($transValue, '_id') ? Str::beforeLast($attribute, '_id') : $attribute), '_')));
                        }
                    }
                    $attributes[$locale][$attribute] = $transValue;
                    ksort($attributes[$locale]);
                }
            }
            $key = Str::plural($classPascal);
            $k = "choice.$key";
            foreach ($locales as $locale) {
                if (Str::contains($key, 'Pivot')) {
                    continue;
                }

                foreach ($withChoice as $v) {
                    if (__("choice.$v", [], $locale) != $v) {
                        if (isset($cashChoice[$locale][$v])) {
                            $choice[$locale][$v] = $cashChoice[$locale][$v];
                            continue;
                        }
                        $choice[$locale][$v] = __("choice.$v", [], $locale);
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

                if (isset($cashChoice[$locale])) {
                    $choice[$locale] = array_merge($cashChoice[$locale], $choice[$locale]);
                }

                if (isset($cashChoice[$locale][$key])) {
                    $choice[$locale][$key] = $cashChoice[$locale][$key];
                    continue;
                }

                if (__($k, [], $locale) != $k) {
                    $choice[$locale][$key] = __($k, [], $locale);
                    continue;
                }
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

        $outputPath = $this->option('output') ?: 'resources/setup/deploy';
        Helpers::writeFile("attributes.php", $attributes, ['output' => $outputPath, 'directories' => !0, 'callback' => fn($e) => $this->components->info("Put file [$e]")]);
        Helpers::writeFile("choice.php", $choice, ['output' => $outputPath, 'directories' => !0, 'callback' => fn($e) => $this->components->info("Put file [$e]")]);
    }
}

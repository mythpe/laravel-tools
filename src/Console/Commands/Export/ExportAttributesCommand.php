<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Commands\Export;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Myth\LaravelTools\Console\BaseCommand;
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

        $attributes = [];
        $choice = [];
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
                if (method_exists($model, 'hasCast') && $model->hasCast($value, ['datetime', 'double', 'float'])) {
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

            foreach ($fillable as $attribute) {
                // if (Str::startsWith($attribute, 'pivot_') || Str::endsWith($attribute, '_pivot')) {
                // continue;
                // }
                foreach ($locales as $locale) {
                    $transKey = "attributes.{$attribute}";
                    $transValue = __($transKey, [], $locale);
                    if ($transValue == $transKey) {
                        $transValue = ucfirst(str_replace('_', ' ', ucwords(Str::snake(Str::endsWith($transValue, '_id') ? Str::beforeLast($attribute, '_id') : $attribute), '_')));
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

<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
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
{--C|countable : with exists countable}
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
     * @throws \ReflectionException
     */
    public function handle(): void
    {
        $toOption = $this->option('to');
        $fromOption = $this->option('from');
        $newOption = $this->option('new');
        $withChoiceOption = $this->option('choice');
        $withCountableOption = $this->option('countable');
        $saveOption = $this->option('save');
        $jsonOption = $this->option('json');
        $rootDisk = Storage::disk('root');
        $appDisk = Storage::disk('app');
        $langDisk = Helpers::langDisk();
        $modelsPaths = config('4myth-tools.auto_discover_models_path', []);
        $modelsFiles = [];
        foreach ($modelsPaths as $path) {
            $modelsFiles = array_merge($modelsFiles, $appDisk->allFiles($path));
        }
        $controllersFiles = $appDisk->allFiles('Http/Controllers');
        $attributes = [];
        $choice = [];
        $additionalChoice = [];
        $locales = Helpers::locales();
        $logArray = [
            'controllers' => [],
            'models'      => [],
        ];

        $cacheAttrs = [
            'ar' => require __DIR__.'/../../../lang/ar/attributes.php',
            'en' => require __DIR__.'/../../../lang/en/attributes.php',
        ];
        $cacheChoice = [
            'ar' => require __DIR__.'/../../../lang/ar/choice.php',
            'en' => require __DIR__.'/../../../lang/en/choice.php',
        ];
        $cacheCountable = [
            'ar' => require __DIR__.'/../../../lang/ar/countable.php',
            'en' => require __DIR__.'/../../../lang/en/countable.php',
        ];
        $translates = collect([
            'current_password',
            'password',
            'password_confirmation',
            'new_password',
            'new_password_confirmation',
            'login_id',
            'control',
            'avatar',
            'avatar_url',
            'status',
            'search',
            'import',
            'export',
            'download',
            'upload',
            'attachments',
        ]);
        foreach ($controllersFiles as $controllersFile) {
            $controllerClass = Str::of($controllersFile)->beforeLast('.php')->replace('/', '\\', $controllersFile)->start('App\\');
            $reflectionControllerClass = new ReflectionClass($controllerClass->toString());
            if (!$reflectionControllerClass->isInstantiable()) {
                continue;
            }
            $controller = app($controllerClass->toString());
            foreach ($reflectionControllerClass->getMethods() as $method) {
                $methodName = $method->getName();
                if (($methodName == 'getRules' || starts_with($methodName, '_')) && $method->getReturnType() == 'array') {
                    $translates = $translates->merge(array_keys($controller->{$methodName}()));
                    $logArray['controllers'][$reflectionControllerClass->getName()] ??= [];
                    $logArray['controllers'][$reflectionControllerClass->getName()][] = $methodName;
                }
            }
        }
        $translates = $translates->unique()->values();
        $modelsBaseNames = [];
        $skipConstants = collect(config('app.4myth.translate.skip_const', []) ?: [])->map(fn($t) => trim($t, '\\'));
        foreach ($modelsFiles as $modelFile) {
            $namespace = Str::of($modelFile)->beforeLast('.php')->replace(['/', '\\\\'], '\\')->start('App\\');
            /** @var BaseModel $model */
            $model = app($namespace->toString());
            $baseName = basename($model::class);
            if (in_array($baseName, ['BaseModel', 'BasePivot'])) {
                continue;
            }
            $modelsBaseNames[] = $baseName;
            $fillable = collect();
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
            $parents = explode('\\', $model::class);
            if (count($parents) > 3) {
                unset($parents[count($parents) - 1]);
                unset($parents[0]);
                unset($parents[1]);
                foreach ($parents as $v) {
                    $additionalChoice[] = $v;
                }
            }
            $fillable = $fillable->filter(fn($value) => !is_numeric($value));
            $class_basename = Str::of(class_basename($model));
            $classBasename = $class_basename->toString();
            $classSnake = $class_basename->snake();
            $classCamel = $class_basename->camel();
            $classPascal = ucfirst($classCamel);
            $fillable = $fillable->merge(["{$classSnake}_id", Str::plural($classSnake)."_id"]);

            // Customizing
            if ($classBasename == 'Setting' && method_exists($model, 'setting')) {
                $fillable = $fillable->merge(array_keys($model::setting()));
            }

            $reflectionClassModel = new ReflectionClass($model);
            $modelConstants = $reflectionClassModel->getConstants();
            $logArray['models'][$reflectionClassModel->getName()] ??= [];
            foreach ($modelConstants as $constantKey => $constant) {
                $fullKey = trim("{$reflectionClassModel->getName()}::$constantKey", '\\');
                if ($skipConstants->contains($fullKey)) {
                    continue;
                }
                if (Str::endsWith(strtolower($constantKey ?: ''), ['_status', '_const'])) {
                    $fillable = $fillable->filter(fn($v) => $v != $constant);
                    continue;
                }
                if (Str::startsWith(strtolower($constantKey ?: ''), ['hash_', 'const_'])) {
                    continue;
                }
                if (is_string($constant) && Str::contains($constant, ['\\'])) {
                    continue;
                }
                elseif (is_string($constant) && preg_match_all("/[\w\d]+/", $constant)) {
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
                $logArray['models'][$reflectionClassModel->getName()][] = $fullKey;
            }

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
            $translates = $translates->merge($fillable)->unique()->values();
        }
        $temp = [];
        $sortArray = [];
        foreach ($translates as $k => $value) {
            $hasFrom = starts_with($value, 'from_');
            $hasTo = starts_with($value, 'to_');
            $strBeforeToFrom = Str::after($value, '_');
            // Sort
            if (($hasFrom || $hasTo) && in_array($strBeforeToFrom, $translates->toArray())) {
                $attributeKey = "{$strBeforeToFrom}_{$value}";
                $sortArray[$attributeKey] = $value;
                $temp[$k] = $attributeKey;
            }
            else {
                $temp[$k] = $value;
            }
        }
        $translates = collect($temp)->filter((fn($v) => !Str::endsWith('.*', $v)))->values()->toArray();
        sort($translates);
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

        // # Set Attributes.
        foreach ($locales as $locale) {
            foreach ($translates as $attribute) {
                if (isset($sortArray[$attribute])) {
                    $attribute = $sortArray[$attribute];
                }
                $transKey = "attributes.$attribute";
                $defaultTrans = $this->defaultTranslate($attribute, $locale);
                $transValue = $defaultTrans;
                $cashValue = $cacheAttrs[$locale][$attribute] ?? null;
                if (trans_has($transKey, $locale)) {
                    $transValue = __($transKey, [], $locale);
                }
                elseif ($cashValue) {
                    $transValue = $cashValue;
                }
                $hasFrom = ends_with($attribute, '_from');
                $hasTo = ends_with($attribute, '_to');
                $strBeforeToFrom = Str::after($attribute, '_');
                if ($hasFrom) {
                    $name = Str::beforeLast($attribute, '_from');
                    if (trans_has($t = "attributes.$strBeforeToFrom", $locale) && !Str::contains($transValue, $v = __($t, [], $locale))) {
                        if ($locale == 'ar') {
                            $transValue = sprintf($v.' %s', $hasFrom ? 'من' : ($hasTo ? 'إلى' : ''));
                        }
                        else {
                            $transValue = sprintf('%s '.$v, $hasFrom ? 'From' : ($hasTo ? 'To' : ''));
                        }
                    }
                    /*elseif (isset($cacheAttrs[$locale][$attribute])) {
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
                    }*/
                }

                // # No value set from cache
                if ($transValue == $defaultTrans && $cashValue) {
                    $transValue = $cashValue;
                }
                if (!$transValue) {
                    dd($transValue, $attribute, $transKey, $defaultTrans);
                }
                $attributes[$locale] ??= [];
                $attributes[$locale][$attribute] = $transValue;
            }
            if (!$newOption && is_file($p = lang_path("$locale/attributes.php"))) {
                $localeFile = include $p;
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
        Helpers::writeFile("attributes.php", $attributes, [
            'output'      => $outputPath,
            'directories' => !0,
            'callback'    => $callback,
        ]);

        foreach ($modelsBaseNames as $modelClass) {
            $baseKey = Str::of($modelClass);
            $plural = $baseKey->plural()->kebab()->title()->replace('-', ' ')->toString();
            $snakePlural = $baseKey->plural()->snake()->finish('_id')->toString();
            $singular = $baseKey->singular()->kebab()->title()->replace('-', ' ')->toString();
            $snakeSingular = $baseKey->singular()->snake()->finish('_id')->toString();
            $key = $baseKey->camel()->plural()->ucfirst()->toString();
            $k = "choice.$key";
            foreach ($locales as $locale) {
                $choice[$locale][$key] ??= null;
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
                if ($cacheChoice[$locale][$key] ?? null) {
                    $choice[$locale][$key] = $cacheChoice[$locale][$key];
                }
                if (!$choice[$locale][$key]) {
                    if (trans_has($k, $locale)) {
                        $choice[$locale][$key] = __($k, [], $locale);
                    }
                    else {
                        $singularValue = trans_has($sca = "attributes.$snakeSingular", $locale) ? __($sca) : $singular;
                        $pluralValue = trans_has($pca = "attributes.$snakePlural", $locale) ? __($pca) : $plural;
                        if ($locale == 'ar') {
                            $choice[$locale][$key] = "$pluralValue|$singularValue";
                        }
                        else {
                            $choice[$locale][$key] = "$singularValue|$pluralValue";
                        }
                    }
                }
                $localeChoice = is_file($p = lang_path("$locale/choice.php")) ? include $p : [];
                $choice[$locale] = [...$choice[$locale], ...$localeChoice];
                if ($withChoiceOption && isset($cacheChoice[$locale])) {
                    $choice[$locale] = [...$cacheChoice[$locale], ...$choice[$locale]];
                }
                if ($withCountableOption && isset($cacheCountable[$locale])) {
                    $choice[$locale] = [...$cacheCountable[$locale], ...$choice[$locale]];
                }
                ksort($choice[$locale]);
            }
        }
        Helpers::writeFile("choice.php", $choice, [
            'output'      => $outputPath,
            'directories' => !0,
            'callback'    => $callback,
        ]);

        ksort($logArray);
        $logExport = var_export($logArray, true);
        $fileContent = "<?php\n\nreturn {$logExport};";
        $rootDisk->put("{$outputPath}/log.php", $fileContent);

        if ($jsonOption) {
            $this->call('myth:lang');
        }
    }

    public function defaultTranslate(string $attribute, string $locale): string
    {
        $attribute = Str::of($attribute);
        $locales = array_map(fn($v) => "_$v", Helpers::locales());
        if ($attribute->lower() == 'myth') {
            return 'MyTh';
        }
        elseif ($attribute->endsWith('.*')) {
            $attribute = $attribute->beforeLast('.*');
        }
        elseif ($attribute->contains('.*.')) {
            $attribute = $attribute->afterLast('.*.');
            if (trans_has($tKey = "attributes.$attribute", $locale, !0)) {
                return __($tKey, [], $locale);
            }
        }
        elseif ($attribute->length() <= 3) {
            return $attribute->upper()->toString();
        }
        $last = $attribute->substr(-3);
        if (in_array($last, ['_ar', '_en'])) {
            $ar = $last == '_ar';
            $attr = $attribute->beforeLast($last);
            $label = trans_has($tk = "attributes.$attr", $locale, !1) ? __($tk, [], $locale) : $attr->title()->replace('_', ' ')->toString();
            return (!$ar ? ($last == '_en' ? "English" : "Arabic") : '')."$label ".($ar ? ($last == '_en' ? "بالإنجليزية" : "بالعربية") : '');
        }
        if (trans_has($tKey = "attributes.$attribute", $locale, !0)) {
            return __($tKey, [], $locale);
        }
        return Str::of($attribute)
            ->replaceMatches('/(\.\*|\.id|_id)$/', '')
            ->replaceMatches('/[^a-zA-Z0-9]+/', ' ')
            ->replaceMatches('/\s+/', ' ')
            ->trim(' .*')->title()->toString();
    }
}

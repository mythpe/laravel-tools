<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Myth\LaravelTools\Console\BaseCommand;
use Myth\LaravelTools\Utilities\ModelCommand;

/**
 *
 */
class MakeModelCommand extends BaseCommand
{
    /**
     *
     */
    const PHP_EOL = "\n";
    /**
     *
     */
    const LINE_COMMENT_UPDATE = 'use myth crud model command';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:model {model?*}
{--B|stubs : Force Create model stubs}
{--s|scoped : Create model with scopes}
{--t|translator : Create model with translator scope}
{--g|generic : Create accessories of generic model}
{--l|lang : Modify model language only}
{--f|files : Modify model files only}
{--d|delete : Delete model}
{--F|force : force mode}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create crud of model.
to insert code automatically add this comment "use myth crud model command" to your files.
-App\Providers\RouteServiceProvider.php
-App\Http\Controllers\SideMenuController.php
';

    /**
     * @var string
     */
    protected string $diskName = 'root';

    /**
     * User input
     *
     * @var array<int, string>
     */
    protected array $argModels = [];

    /**
     * User input
     *
     * @var array<int, ModelCommand>
     */
    protected array $models = [];

    /**
     * Current Model
     */
    protected ?ModelCommand $model = null;

    /** @var bool */
    protected bool $stubsOnly = !1;

    /** @var bool */
    protected bool $filesOnly = !1;

    /** @var bool */
    protected bool $langOnly = !1;

    /** @var bool */
    protected bool $hasTranslator = !1;

    /** @var bool */
    protected bool $hasScopes = !1;

    /** @var bool */
    protected bool $isGeneric = !1;

    /** @var bool */
    protected bool $isDelete = !1;

    /** @var bool */
    protected bool $isForce = !1;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $model = $this->argument('model');
        if (is_array($model)) {
            $this->argModels = $model;
        }
        $this->stubsOnly = (bool) $this->option('stubs');
        $this->filesOnly = (bool) $this->option('files');
        $this->langOnly = (bool) $this->option('lang');
        $this->hasTranslator = (bool) $this->option('translator');
        $this->hasScopes = (bool) $this->option('scoped');
        $this->isGeneric = (bool) $this->option('generic');
        $this->isDelete = (bool) $this->option('delete');
        $this->isForce = (bool) $this->option('force');

        if (empty($model)) {
            $disk = Storage::disk('root');
            $modelFiles = $disk->files('app/Models');
            $auto = 'auto';
            $none = 'none';
            $choice = $this->components->choice(
                'Choice Models',
                array_merge($modelFiles, [
                    $auto => 'Auto',
                    $none => 'None',
                ]),
                $none,
                1,
                !0
            );
            if (in_array($none, $choice)) {
                $this->components->info("Bye");
                return;
            }
            $models = [];
            if (in_array($auto, $choice)) {
                $choice = $modelFiles;
            }
            foreach ($choice as $value) {
                $models[] = Str::singular(class_basename(pathinfo($value, PATHINFO_FILENAME)));
            }
            $this->stubsOnly = !0;
            $this->argModels = $models;
        }

        $this->prepare();
        foreach ($this->models as $value) {
            $this->model = $value;
            if ($this->langOnly()) {
                $this->insertModelLanguage();
                continue;
            }
            $migrationPrefix = "1111_00_00_000000";
            $migrations = $this->disk()->files('database/migrations');
            if (count($migrations) > 0) {
                asort($migrations);
                $last = pathinfo(array_pop($migrations), PATHINFO_FILENAME);
                $name = array_filter(explode('_', preg_replace(['/[^\d_]+/', '/__/'], '', $last)));
                if (count($name) == 4) {
                    $name[3] = str_pad(intval($name[3]) + 1, strlen($name[3]), '0', STR_PAD_LEFT);
                    $migrationPrefix = implode('_', $name);
                }
            }
            $modelName = $value->name;
            $namespacePath = $value->namespace ? str_ireplace('\\', '/', trim($value->namespace, '\\')).'/' : '';
            $stubs = [
                'ModelClass.stub'         => "app/Models/$namespacePath$modelName.php",
                'ModelMigration.stub'     => "database/migrations/{$migrationPrefix}_create_{$value->snakePlural}_table.php",
                'ModelController.stub'    => "app/Http/Controllers/$namespacePath{$modelName}Controller.php",
                'ModelResource.stub'      => "app/Http/Resources/$namespacePath{$modelName}Resource.php",
                'BelongsToModel.stub'     => "app/Traits/BelongsTo/{$namespacePath}BelongsTo{$modelName}.php",
                'BelongsToManyModel.stub' => "app/Traits/BelongsToMany/{$namespacePath}BelongsToMany{$modelName}.php",
                'HasManyModel.stub'       => "app/Traits/HasMany/{$namespacePath}HasMany{$modelName}.php",
            ];
            if ($this->isDeleteMode()) {
                if (!$this->isForce() && !$this->confirm("Delete <fg=red>{$value->string}</> ?"))
                    continue;
            }
            $stubsPath = __DIR__.'/../../Stubs';
            foreach ($stubs as $stub => $path) {
                $isMigration = $stub === 'ModelMigration.stub';
                if ($isMigration) {
                    $migrations = $this->disk()->files('database/migrations');
                    foreach ($migrations as $migration) {
                        $name = preg_replace('(\d+_)', '', pathinfo($migration, PATHINFO_FILENAME));
                        if (
                            Str::contains($name, [
                                "{$value->snakeSingular}_table",
                                "{$value->snakePlural}_table",
                                $value->snakeSingular,
                                $value->snakePlural,
                            ])
                        ) {
                            $path = $migration;
                            break;
                        }
                    }
                }

                if ($this->stubsOnly()) {
                    $traits = ['BelongsToModel.stub', 'BelongsToManyModel.stub', 'HasManyModel.stub'];
                    if (!in_array($stub, $traits)) {
                        continue;
                    }
                }

                if ($this->isDeleteMode()) {
                    $this->components->task("<fg=red>Deleting</> $path", fn() => $this->disk()->exists($path) ? $this->disk()->delete($path) : !1);
                    continue;
                }
                $content = $this->fillStub(file_get_contents("$stubsPath/$stub"));
                if ($this->stubsOnly()) {
                    $this->components->task("<fg=green>Replacing</> $path", fn() => $this->disk()->put($path, $content));
                    continue;
                }
                $exists = $this->disk()->exists($path);
                $taskTitle = $exists ? "<fg=yellow>File exists:</> $path" : "<fg=green>Creating</> $path";
                $this->components->task($taskTitle, fn() => !$exists && $this->disk()->put($path, $content));
            }
            if ($this->stubsOnly()) {
                continue;
            }
            $this->updateRouteServiceProvider();
            $this->updateSideMenuController();
            $this->insertModelLanguage();
            $this->newLine();
        }

        if (count($this->models) > 0 && !$this->isDeleteMode() && !$this->langOnly() && !$this->stubsOnly()) {
            $this->components->info("Please insert model routes: [<fg=yellow;bg=black>routes.php</>]");
            foreach ($this->models as $value) {
                $modelNamespace = "App\\Http\\Controllers\\{$value->string}Controller";
                $v = "apiResource('$value->studlySingular', $modelNamespace::class);";
                $this->line("<fg=yellow;bg=black>$v</>");
            }
            $this->components->info("Please run <fg=yellow;bg=black>php artisan setup:permissions</> to make permissions or add them manually.");
        }
    }

    /**
     * @return void
     */
    public function updateSideMenuController(): void
    {
        $path = 'app\Http\Controllers\SideMenuController.php';
        $modelName = $this->model->studlySingular;
        $namespace = $this->model->namespace;
        $existsNeedles = "// # $modelName.";
        $routeName = $this->model->kebabPlural;
        $permissions = "$modelName.index";
        if ($namespace) {
            $routeName = trim($namespace->replace('\\', '.')->lower()->kebab().".$routeName", '.');
            $permissions = trim($namespace->replace('\\', '.').".$permissions", '.');
        }
        $replaceContent = <<<html
            $existsNeedles
            [
                'title'       => trans_choice("choice.{$this->model->studlyPlural}", 2),
                'name'        => 'panel.$routeName',
                'icon'        => '',
                'permissions' => ['$permissions'],
            ],
html;
        $this->modifyFile($path, $replaceContent);
    }

    /**
     * @return void
     */
    protected function updateRouteServiceProvider(): void
    {
        $path = 'app\Providers\RouteServiceProvider.php';
        $model = $this->model->string;
        $modelName = $this->model->studlySingular;
        $replaceContent = '        $this->binder(\''.$modelName.'\', \\App\\Models\\'.$model.'::class);';
        $this->modifyFile($path, $replaceContent);
    }

    /**
     * Prepare model and namespace of model
     *
     * @return void
     */
    protected function prepare(): void
    {
        foreach ($this->argModels as $value) {
            $this->models[] = new ModelCommand($value);
        }
    }

    /**
     * Fill stub content
     *
     * @param string $stub
     *
     * @return string
     */
    protected function fillStub(string $stub): string
    {
        $class_methods = $class_use = $fillable = $attributes = $casts = $rules = $migration = $resource = $oldest = '';
        $year = now()->format('Y');
        $copyright = <<<Copyright
/*
 * MyTh Ahmed Faiz Copyright © 2016-$year All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */
Copyright;

        if ($this->hasScopes()) {
            $class_use .= 'use \Myth\LaravelTools\Traits\Utilities\OrderByScopeTrait, \Myth\LaravelTools\Traits\Utilities\ActiveScopeTrait;
';
            $fillable .= <<<html

        'active',
        'order_by',
html;
            $attributes .= <<<html

        'active'   => !0,
        'order_by' => 0,
html;
            $casts .= <<<html

        'active'   => 'bool',
        'order_by' => 'int',
html;
            $rules .= <<<html

            'active'   => ['bool'],
            'order_by' => ['int'],
html;
            $migration .= '
            $table->boolean(\'active\');
            $table->integer(\'order_by\');';
            $resource .= '
            \'status\' => $model->active_to_string,';
            $oldest .= '
        //$this->oldest = \'order_by\';';

        }

        if ($this->hasTranslator()) {
            $class_use .= 'use Myth\LaravelTools\Traits\Utilities\HasTranslatorTrait;
';
            $class_methods .= "
    public static function translatorAttributes(): array
    {
        return ['name',];
    }
";
        }
        $vars = array_keys(get_class_vars(ModelCommand::class));
        $keys = Arr::map($vars, fn($k) => "{".$k."}");
        $values = Arr::map($vars, fn($k) => $this->model->{$k});
        return str_ireplace(array_merge([
            '{copyright}',
            '{class_use}',
            '{fillable}',
            '{attributes}',
            '{casts}',
            '{rules}',
            '{migration}',
            '{resource}',
            '{oldest}',
            '{class_methods}',
        ], $keys), array_merge([
            $copyright,
            $class_use,
            $fillable,
            $attributes,
            $casts,
            $rules,
            $migration,
            $resource,
            $oldest,
            $class_methods,
        ], $values), $stub);
    }

    /**
     * @param string $path
     * @param $replaceContent
     * @return void
     */
    protected function modifyFile(string $path, $replaceContent = null): void
    {
        $this->components->task("<fg=yellow>Updating</> $path", function () use ($path, $replaceContent) {
            $comment = static::LINE_COMMENT_UPDATE;
            // Get Source
            if (!($source = file(str_replace('\\', '/', $this->disk()->path($path))))) {
                return !1;
            }

            $commentIndex = null;
            $existsLine = null;
            $tempPath = storage_path('framework/cache/myth-lang.temp');
            file_put_contents($tempPath, $replaceContent);
            $temp = file($tempPath);
            $firstTemp = $temp[0] ?? '';
            foreach ($source as $k => $line) {
                is_null($commentIndex) && ($commentIndex = Str::contains($line, $comment) ? $k : null);
                is_null($existsLine) && ($existsLine = trim($line) == trim($firstTemp) ? $k : null);
            }

            if ($this->isDeleteMode()) {
                if (is_null($existsLine)) {
                    return !1;
                }
                for ($i = 0; $i < count($temp); $i++) {
                    unset($source[$existsLine + $i]);
                }
                unlink($tempPath);
                return $this->disk()->put($path, implode('', $source));
            }
            else {
                unlink($tempPath);
                if ($this->isDeleteMode() || is_null($commentIndex) || !is_null($existsLine)) {
                    return !1;
                }
                $afterComment = $commentIndex + 1;
                $before = array_slice($source, 0, $afterComment);
                $after = array_slice($source, $afterComment);
                return $this->disk()->put($path, implode('', array_merge($before, [$replaceContent, self::PHP_EOL], $after)));
            }
        });
    }

    /**
     * @return void
     */
    protected function insertModelLanguage(): void
    {
        if ($this->isDeleteMode() && $this->filesOnly()) {
            return;
        }
        $this->components->info("Model language");
        $this->components->task("Attributes File: ", function () {
            $success = !1;
            foreach (config('4myth-tools.locales') as $locale) {
                $path = lang_path("$locale/attributes.php");
                $array = require $path;;
                if (!is_array($file = file($path))) {
                    continue;
                }
                $id = "{$this->model->snakeSingular}_id";
                $ids = "{$this->model->snakePlural}_id";
                $modify = !array_key_exists($id, $array) || !array_key_exists($ids, $array);
                $delete = array_key_exists($id, $array) || array_key_exists($ids, $array);
                if ($this->isDeleteMode() && $delete) {
                    foreach ($file as $fileKey => $line) {
                        if (!is_array($file)) {
                            continue;
                        }
                        if (Str::contains($line, $id)) {
                            unset($file[$fileKey]);
                        }
                        elseif (Str::contains($line, $ids)) {
                            unset($file[$fileKey]);
                        }
                        $success = file_put_contents($path, implode('', $file)) !== !1;
                    }
                }
                elseif (!$this->isDeleteMode() && $modify) {
                    $src = require __DIR__."/../../lang/$locale/attributes.php";
                    $last = array_pop($file);
                    $lastFileIndex = count($file) - 1;
                    if (!is_array($file)) {
                        continue;
                    }
                    $rtrimList = [','];
                    foreach ($rtrimList as $rtrim) {
                        if (!Str::endsWith(rtrim($file[$lastFileIndex]), $rtrim)) {
                            $file[$lastFileIndex] .= $rtrim;
                        }
                    }

                    if (!array_key_exists($id, $array)) {
                        $val = array_key_exists($id, $src) ? $src[$id] : $this->model->titleSingular;
                        $file[] = "'$id' => '$val',".self::PHP_EOL;
                    }
                    if (!array_key_exists($ids, $array)) {
                        $val = array_key_exists($ids, $src) ? $src[$ids] : $this->model->titlePlural;
                        $file[] = "'$ids' => '$val',".self::PHP_EOL;
                    }
                    if ($modify) {
                        $file[] = $last;
                        $success = file_put_contents($path, implode('', $file)) !== !1;
                    }
                }
            }
            return $success;
        });
        $this->components->task("Choice File: ", function () {
            $success = !1;
            foreach (config('4myth-tools.locales') as $locale) {
                $path = lang_path("$locale/choice.php");
                $array = require $path;
                $file = file($path);
                if (is_array($file)) {
                    $k = (string) $this->model->studlyPlural;
                    if ($this->isDeleteMode()) {
                        if (array_key_exists($k, $array)) {
                            foreach ($file as $lineKey => $line) {
                                if (Str::contains($line, $k)) {
                                    unset($file[$lineKey]);
                                }
                            }
                            $success = file_put_contents($path, implode('', $file)) !== !1;
                        }
                    }
                    else {
                        if (!array_key_exists($k, $array)) {
                            $src = require __DIR__."/../../lang/$locale/choice.php";
                            $value = array_key_exists($k, $src) ? $src[$k] : ($locale == 'ar' ? "{$this->model->titlePlural}|{$this->model->titleSingular}" : "{$this->model->titleSingular}|{$this->model->titlePlural}");
                            if (!is_array($file)) {
                                continue;
                            }
                            $last = array_pop($file);
                            $lastIndex = count($file) - 1;
                            $rtrimList = [','];
                            foreach ($rtrimList as $rtrim) {
                                if (!Str::endsWith(rtrim($file[$lastIndex]), $rtrim)) {
                                    $file[$lastIndex] .= $rtrim;
                                }
                            }
                            $success = file_put_contents($path, implode('', array_merge($file, [
                                    "'$k' => '$value',".self::PHP_EOL,
                                    $last,
                                ]))) !== !1;
                        }
                    }
                }
            }
            return $success;
        });
    }

    /**
     * @return bool
     */
    protected function isForce(): bool
    {
        return $this->isForce;
    }

    /**
     * @return bool
     */
    protected function isDeleteMode(): bool
    {
        return $this->isDelete;
    }

    /**
     * @return bool
     */
    protected function isGeneric(): bool
    {
        return $this->isGeneric;
    }

    /**
     * @return bool
     */
    protected function hasScopes(): bool
    {
        return $this->hasScopes;
    }

    /**
     * @return bool
     */
    protected function hasTranslator(): bool
    {
        return $this->hasTranslator;
    }

    /**
     * @return bool
     */
    protected function langOnly(): bool
    {
        return $this->langOnly;
    }

    /**
     * @return bool
     */
    protected function filesOnly(): bool
    {
        return $this->filesOnly;
    }

    /**
     * @return bool
     */
    protected function stubsOnly(): bool
    {
        return $this->stubsOnly;
    }
}

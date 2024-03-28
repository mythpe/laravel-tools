<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Myth\LaravelTools\Console\BaseCommand;

class MakeModelCommand extends BaseCommand
{
    /**
     *
     */
    const LINE_COMMENT_UPDATE = 'use myth crud model command';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'myth:model {model*}
{--s|scoped : Create model with scopes}
{--t|translator : Create model with translator scope}
{--d|delete : Delete model} {--F|force-delete : Force delete model}';

    //use myth crud model command

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
     * @var array<int, array<int, string>>
     */
    protected array $models = [];

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): void
    {
        $this->prepare();
        $force = (bool) $this->option('force-delete');
        $deleteModel = $force ? !0 : ((bool) $this->option('delete'));
        foreach ($this->models as $value) {
            $model = $value['model'];
            $modelName = $value['modelName'];
            $namespace = $value['namespace'] ?? null;

            $snake = Str::snake(Str::pluralStudly($model));
            $stubs = [
                'ModelMigration.stub'     => "database/migrations/1111_00_00_000000_{$snake}_table.php",
                'ModelClass.stub'         => "app/Models/$model.php",
                'ModelController.stub'    => "app/Http/Controllers/{$model}Controller.php",
                'ModelResource.stub'      => "app/Http/Resources/{$model}Resource.php",
                'BelongsToModel.stub'     => "app/Traits/BelongsTo/BelongsTo{$modelName}.php",
                'BelongsToManyModel.stub' => "app/Traits/BelongsToMany/BelongsToMany{$modelName}.php",
                'HasManyModel.stub'       => "app/Traits/HasMany/HasMany{$modelName}.php",
            ];

            if ($deleteModel) {
                if (!$force && !$this->confirm("Delete <fg=red>$model</> ?"))
                    continue;
            }
            $stubsPath = __DIR__.'/../../Stubs';
            foreach ($stubs as $stub => $path) {
                if ($deleteModel) {
                    $this->components->task("Deleting <fg=red>$path</>", fn() => $this->disk()->delete($path));
                    continue;
                }
                $content = $this->fillStub($value, file_get_contents("$stubsPath/$stub"));
                if ($this->disk()->exists($path)) {
                    $this->components->warn("File exists: <fg=red>$path</> <fg=yellow;bg=black>Skipped</>");
                    continue;
                }
                $this->components->task("Creating $path", fn() => $this->disk()->put($path, $content));
            }

            $path = 'app\Providers\RouteServiceProvider.php';
            $existsNeedles = "$modelName::";
            $replaceContent = '        $this->binder(\''.$modelName.'\', \\App\\Models\\'.$model.'::class);';
            $this->modifyFile($modelName, $path, $existsNeedles, $replaceContent);

            $path = 'app\Http\Controllers\SideMenuController.php';
            $existsNeedles = "// # $modelName.";
            $routeName = $this->modelPluralKebabName($modelName);
            $permissions = "$modelName.index";
            if ($namespace) {
                $routeName = strtolower(str_ireplace('\\', '.', $namespace)).".$routeName";
                $permissions = str_ireplace('\\', '.', $namespace).".$permissions";
            }
            $permissions = "'$permissions'";

            $replaceContent = <<<html
            $existsNeedles
            [
                'title'       => trans_choice("choice.{$this->modelPluralName($modelName)}", 2),
                'name'        => 'panel.$routeName',
                'icon'        => '',
                'permissions' => [$permissions],
            ],
html;
            $this->modifyFile($modelName, $path, $existsNeedles, $replaceContent);
            $this->insertModelLanguage($modelName);
            $this->newLine();
            $modelNamespace = "\\App\\Http\\Controllers\\{$model}Controller";
        }

        if (!$force && !$deleteModel && count($this->models) > 0) {
            $this->components->info("Please insert model routes: [<fg=yellow;bg=black>routes.php</>]");
            foreach ($this->models as $value) {
                $model = $value['model'];
                $modelName = $value['modelName'];
                $modelNamespace = "App\\Http\\Controllers\\{$model}Controller";
                $v = "apiResource('$modelName', $modelNamespace::class);";
                $this->line("<fg=yellow;bg=black>$v</>");
            }
            $this->components->info("Please run <fg=yellow;bg=black>php artisan setup:permissions</> to make permissions or add them manually.");
        }
    }

    /**
     * Prepare model and namespace of model
     *
     * @return void
     */
    protected function prepare(): void
    {
        $arg = $this->argument('model');
        foreach ($arg as $value) {
            $model = preg_replace(['/\/+/', '/\\\+/'], '\\', $value);
            $options = explode('\\', $model);
            $data = [
                'model'     => $model,
                'modelName' => array_pop($options),
                'namespace' => null,
            ];
            if (count($options) > 0) {
                $data['namespace'] = implode('\\', $options);
            }
            $this->models[] = $data;
        }
        // d($this->models);
        // $this->model = preg_replace(['/\/+/', '/\\\+/'], '\\', $this->argument('model'));
        // $options = explode('\\', $this->model);
        // $modelName = array_pop($options);
        // if (count($options) > 0) {
        //     $namespace = implode('\\', $options);
        // }
    }

    /**
     * Fill stub content
     *
     * @param array<string,mixed> $Model
     * @param string $stub
     *
     * @return string
     */
    protected function fillStub(array $Model, string $stub): string
    {
        $scoped = $this->option('scoped');
        $class_methods = $class_use = $fillable = $attributes = $casts = $rules = $migration = $resource = $oldest = '';
        $model = $Model['model'];
        $modelName = $Model['modelName'];
        $namespace = $Model['namespace'];

        if ($scoped) {
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
            \'status_to_string\' => $model->active_to_string,';
            $oldest .= '
        //$this->oldest = \'order_by\';';

        }

        if ($this->option('translator')) {
            $class_use .= 'use Myth\LaravelTools\Traits\Utilities\HasTranslatorTrait;
';
            $class_methods .= "
    public static function translatorAttributes(): array
    {
        return ['name',];
    }
";
        }

        return str_ireplace([
            '{namespace}',
            '{model}',
            '{modelName}',
            '{year}',
            '{class_use}',
            '{fillable}',
            '{attributes}',
            '{casts}',
            '{rules}',
            '{migration}',
            '{resource}',
            '{oldest}',
            '{modelForeignKey}',
            '{modelCamelName}',
            '{modelPluralName}',
            '{class_methods}',
        ], [
            $namespace ? '\\'.$namespace : null,
            $model,
            $modelName,
            Carbon::now()->format('Y'),
            $class_use,
            $fillable,
            $attributes,
            $casts,
            $rules,
            $migration,
            $resource,
            $oldest,
            $this->modelForeignKey($modelName),
            $this->modelCamelName($modelName),
            Str::camel($this->modelPluralName($modelName)),
            $class_methods,
        ], $stub);
    }

    /**
     * @param string $modelName
     * @return string
     */
    protected function modelForeignKey(string $modelName): string
    {
        return Str::snake($modelName).'_id';
    }

    /**
     * @param string $modelName
     * @return string
     */
    protected function modelCamelName(string $modelName): string
    {
        return Str::camel($modelName);
    }

    /**
     * @param string $modelName
     * @return string
     */
    protected function modelPluralName(string $modelName): string
    {
        return Str::plural($modelName);
    }

    /**
     * @param string $modelName
     * @param string $path
     * @param $existsNeedles
     * @param $replaceContent
     * @return void
     */
    protected function modifyFile(string $modelName, string $path, $existsNeedles = null, $replaceContent = null): void
    {
        $this->components->task("Updateing <fg=green>$path</>", function () use ($modelName, $path, $existsNeedles, $replaceContent) {
            $comment = static::LINE_COMMENT_UPDATE;
            $this->newLine();
            $deleteMode = $this->isDeleteMode();

            // Get Source
            $source = file(str_replace('\\', '/', $this->disk()->path($path)));
            $commentIndex = null;
            $existsLine = null;
            foreach ($source as $k => $line) {
                is_null($existsLine) && ($existsLine = Str::contains($line, $existsNeedles) ? $k : null);
                is_null($commentIndex) && ($commentIndex = Str::contains($line, $comment) ? $k : null);
            }

            if (!is_null($existsLine)) {
                if ($deleteMode) {
                    $f = str_ireplace($replaceContent, '', implode('', $source));
                    $this->disk()->put($path, $f);
                }
                else {
                    ++$existsLine;
                    $this->components->warn("<fg=red>$modelName</> found in line: {$existsLine}");
                }
            }
            elseif (is_null($commentIndex)) {
                $this->components->info("Add this comment [$comment] to automatically modify the file or add this line: [$replaceContent]");
            }
            else {
                if ($this->isDeleteMode() && is_null($existsLine)) {
                    return;
                }
                $before = array_slice($source, 0, $commentIndex + 1);
                $after = array_slice($source, $commentIndex + 1);
                $file = array_merge($before, [
                    $replaceContent,
                    PHP_EOL,
                ], $after);
                $lastContent = $file;
                if ($deleteMode) {
                    $lastContent = str_ireplace(trim($replaceContent), '', $lastContent);
                }
                $this->disk()->put($path, implode('', $lastContent));
                $this->components->twoColumnDetail("<fg=green>$path</>", '<fg=green>Updated</>');
            }
        });
        $this->newLine();
    }

    /**
     * @param string $modelName
     * @return string
     */
    protected function modelPluralKebabName(string $modelName): string
    {
        return Str::kebab($this->modelPluralName($modelName));
    }

    /**
     * @param string $modelName
     * @return void
     */
    protected function insertModelLanguage(string $modelName): void
    {
        $this->components->task("Model language", function () use ($modelName) {
            $pluralChoice = $this->modelPluralName($modelName);
            $this->newLine();
            $studlyWords = ucwords(str_ireplace('-', ' ', Str::kebab(Str::studly($modelName))));
            $pluralWords = ucwords(str_ireplace('-', ' ', $this->modelPluralKebabName($modelName)));
            foreach (config('4myth-tools.locales') as $locale) {
                $choice = "lang/$locale/choice.php";
                if (!$this->disk()->exists($choice)) {
                    $this->components->twoColumnDetail("<fg=red>$choice</> not exists", '<fg=red>Skipped</>');
                }
                else {
                    $choiceContent = file($this->disk()->path($choice));
                    $choiceArray = require lang_path("$locale/choice.php");
                    $choiceFile = '';
                    foreach ($choiceContent as $content) {
                        if (Str::contains($content, 'return')) {
                            break;
                        }
                        $choiceFile .= $content;
                    }
                    if ($this->isDeleteMode()) {
                        unset($choiceArray[$pluralChoice]);
                    }
                    else {
                        if (array_key_exists($pluralChoice, $choiceArray)) {
                            $this->components->twoColumnDetail("<fg=red>$pluralChoice</> Trans choice exists", '<fg=red>Skipped</>');
                        }
                        else {
                            $choiceValue = $locale == 'ar' ? 'مفرد|جمع' : "$studlyWords|$pluralWords";
                            $choiceArray[$pluralChoice] = $choiceValue;
                        }
                    }
                    $choiceArrayContent = [];
                    $separator = ','.PHP_EOL;
                    foreach ($choiceArray as $key => $value) {
                        $choiceArrayContent[] .= "'$key' => '$value'";
                    }
                    $choiceArrayContent = implode($separator, $choiceArrayContent);
                    if (!Str::endsWith(trim($choiceArrayContent), ',')) {
                        $choiceArrayContent .= ',';
                    }
                    $choiceFile .= <<<html
return [
$choiceArrayContent
];
html;
                    $this->disk()->put($choice, $choiceFile);
                    $this->components->twoColumnDetail($choice, '<fg=green>Updated</>');
                }


                $attribute = "lang/$locale/attributes.php";
                $attr = $this->modelForeignKey($modelName);
                $attrs = Str::plural(Str::beforeLast($attr, '_id')).'_id';
                if (!$this->disk()->exists($attribute)) {
                    $this->components->twoColumnDetail("<fg=red>$attribute</> not exists", '<fg=red>Skipped</>');
                    return;
                }


                $attributesContent = file($this->disk()->path($attribute));
                $attributesArray = require lang_path("$locale/attributes.php");
                $attributesFile = '';
                foreach ($attributesContent as $content) {
                    if (Str::contains($content, 'return')) {
                        break;
                    }
                    $attributesFile .= $content;
                }

                if ($this->isDeleteMode()) {
                    unset($attributesArray[$attr]);
                    unset($attributesArray[$attrs]);
                }
                else {
                    if (array_key_exists($attr, $attributesArray)) {
                        $this->components->twoColumnDetail("<fg=red>$attr</> attribute exists", '<fg=red>Skipped</>');
                    }
                    else {
                        $attributesArray[$attr] = $studlyWords;
                    }

                    if (array_key_exists($attrs, $attributesArray)) {
                        $this->components->twoColumnDetail("<fg=red>$attrs</> attribute exists", '<fg=red>Skipped</>');
                    }
                    else {
                        $attributesArray[$attrs] = $pluralWords;
                    }
                }

                $attributesArrayContent = [];
                $separator = ','.PHP_EOL;
                foreach ($attributesArray as $key => $value) {
                    $attributesArrayContent[] .= "'$key' => '$value'";
                }
                $attributesArrayContent = implode($separator, $attributesArrayContent);
                if (!Str::endsWith(trim($attributesArrayContent), ',')) {
                    $attributesArrayContent .= ',';
                }
                $attributesFile .= <<<html
return [
$attributesArrayContent
];
html;
                $this->disk()->put($attribute, $attributesFile);
                $this->components->twoColumnDetail($attribute, '<fg=green>Updated</>');
            }
        });
    }

    /**
     * @return bool
     */
    protected function isDeleteMode(): bool
    {
        return $this->option('force-delete') || $this->option('delete');
    }
}

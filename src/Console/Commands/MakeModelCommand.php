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
use Myth\LaravelTools\Utilities\ModelCommand;

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
{--g|generic : Create accessories of generic model}
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
     * @var array<int, ModelCommand>
     */
    protected array $models = [];

    /**
     * Current Model
     */
    protected ?ModelCommand $model = null;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle(): void
    {
        $this->prepare();
        foreach ($this->models as $value) {
            $this->model = $value;
            $migrationPrefix = "1111_00_00_000000";
            $migrations = $this->disk()->files('database/migrations');
            if (count($migrations) > 0) {
                asort($migrations);
                $last = pathinfo($migrations[count($migrations) - 1], PATHINFO_FILENAME);
                $name = explode('_', preg_replace(['/[^\d_]+/', '/__/'], '', $last));
                if (count($name) == 4) {
                    $name[3] = str_pad(intval($name[3]) + 1, strlen($name[3]), '0', STR_PAD_LEFT);
                    $migrationPrefix = implode('_', $name);
                }
            }
            $stubs = [
                'ModelClass.stub'         => "app/Models/{$value->studly}.php",
                'ModelController.stub'    => "app/Http/Controllers/{$value->studly}Controller.php",
                'ModelResource.stub'      => "app/Http/Resources/{$value->studly}Resource.php",
                'BelongsToModel.stub'     => "app/Traits/BelongsTo/BelongsTo{$value->studly}.php",
                'BelongsToManyModel.stub' => "app/Traits/BelongsToMany/BelongsToMany{$value->studly}.php",
                'HasManyModel.stub'       => "app/Traits/HasMany/HasMany{$value->studly}.php",
                'ModelMigration.stub'     => "database/migrations/{$migrationPrefix}_create_{$value->snakePlural}_table.php",
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

                if ($this->isDeleteMode()) {
                    $this->components->task("<fg=red>Deleting</> $path", fn() => $this->disk()->exists($path) ? $this->disk()->delete($path) : !1);
                    continue;
                }
                $content = $this->fillStub(file_get_contents("$stubsPath/$stub"));
                $exists = $this->disk()->exists($path);
                $taskTitle = $exists ? "<fg=yellow>File exists:</> $path" : "<fg=green>Creating</> $path";
                $this->components->task($taskTitle, fn() => !$exists && $this->disk()->put($path, $content));
            }
            // $this->line('');

            // $this->updateRouteServiceProvider($value);
            // $this->updateSideMenuController($value);
            // $this->insertModelLanguage($modelName);
            $this->newLine();
        }

        if (count($this->models) > 0 && !$this->isDeleteMode()) {
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

    public function updateSideMenuController(array $Model): void
    {
        $path = 'app\Http\Controllers\SideMenuController.php';
        $modelName = $Model['modelName'];
        $namespace = $Model['namespace'] ?? null;
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
    }

    protected function updateRouteServiceProvider(array $Model): void
    {
        $path = 'app\Providers\RouteServiceProvider.php';
        $model = $Model['model'];
        $modelName = $Model['modelName'];
        $existsNeedles = "$modelName::";
        $replaceContent = '        $this->binder(\''.$modelName.'\', \\App\\Models\\'.$model.'::class);';
        $this->modifyFile($modelName, $path, $existsNeedles, $replaceContent);
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
            $this->models[] = new ModelCommand($value);
            // $model = preg_replace(['/\/+/', '/\\\+/'], '\\', $value);
            // $options = explode('\\', $model);
            // $data = [
            //     'model'     => $model,
            //     'modelName' => array_pop($options),
            //     'namespace' => null,
            // ];
            // if (count($options) > 0) {
            //     $data['namespace'] = implode('\\', $options);
            // }
            // $this->models[] = $data;
        }
    }

    /**
     * Fill stub content
     *
     * @param ModelCommand $Model
     * @param string $stub
     *
     * @return string
     */
    protected function fillStub(string $stub): string
    {
        $class_methods = $class_use = $fillable = $attributes = $casts = $rules = $migration = $resource = $oldest = '';

        if ($this->isScoped()) {
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

        if ($this->isTranslator()) {
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
            $this->model->namespace ? '\\'.$this->model->namespace : null,
            $this->model->name,
            $this->model->studly,
            Carbon::now()->format('Y'),
            $class_use,
            $fillable,
            $attributes,
            $casts,
            $rules,
            $migration,
            $resource,
            $oldest,
            $this->modelForeignKey($this->model->studly),
            $this->modelCamelName($this->model->studly),
            Str::camel($this->modelPluralName($this->model->studly)),
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

    protected function modelSingularName(string $modelName): string
    {
        return Str::singular($modelName);
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
        $this->components->task("<fg=yellow>Updateing</> $path", function () use ($modelName, $path, $existsNeedles, $replaceContent) {
            $comment = static::LINE_COMMENT_UPDATE;
            $deleteMode = $this->isDeleteMode();
            // Get Source
            $source = file(str_replace('\\', '/', $this->disk()->path($path)));
            if (!is_array($source)) {
                $source = [];
            }
            $commentIndex = null;
            $existsLine = null;
            foreach ($source as $k => $line) {
                is_null($existsLine) && ($existsLine = Str::contains($line, trim($existsNeedles)) ? $k : null);
                is_null($commentIndex) && ($commentIndex = Str::contains($line, $comment) ? $k : null);
            }

            if (!is_null($existsLine) && $this->isDeleteMode()) {
                $content = trim($source[$existsLine]) == trim($replaceContent);
                if (!$content) {
                    for ($i = 0; $i < 7; $i++) {
                        unset($source[$existsLine + $i]);
                    }
                    return $this->disk()->put($path, implode('', $source));
                }
                else {
                    unset($source[$existsLine]);
                }
                return $this->disk()->put($path, implode('', $source));
            }
            else {
                if ($this->isDeleteMode() || is_null($commentIndex)) {
                    return !1;
                }
                $afterComment = $commentIndex + 1;
                $before = array_slice($source, 0, $afterComment);
                $after = array_slice($source, $afterComment);
                return $this->disk()->put($path, implode('', array_merge($before, [$replaceContent, PHP_EOL], $after)));
            }
        });
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
            $studlyWords = ucwords(str_ireplace('-', ' ', Str::kebab(Str::studly($modelName))));
            $pluralWords = ucwords(str_ireplace('-', ' ', $this->modelPluralKebabName($modelName)));
            foreach (config('4myth-tools.locales') as $locale) {
                // $this->updateLanguageFile('choice', $locale, $modelName);
                $this->updateLanguageFile($modelName);
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

    protected function updateLanguageFile(string $fileName, string $locale, string $modelName): void
    {
        $files = ['choice', 'attributes'];

        $modelStr = Str::of($modelName);
        $pluralModelName = $modelStr->pluralStudly();
        $singularModelName = $modelStr->singular();

        $singularSnake = $modelStr->singular()->snake();
        $pluralSnake = $modelStr->plural()->snake();

        $singularWords = $modelStr->singular()->snake(' ')->title();
        $pluralWords = $modelStr->pluralStudly()->snake(' ')->title();
        d([
            (string) $pluralModelName,
            (string) $singularModelName,
            (string) $singularSnake,
            (string) $pluralSnake,
            (string) $singularWords,
            (string) $pluralWords,
        ]);

        foreach (config('4myth-tools.locales') as $locale) {

        }

        $path = "lang/$locale/$fileName.php";
        $langPath = lang_path("$locale/$fileName.php");


        $pluralChoice = $this->modelPluralName($modelName);
        $studlyWords = ucwords(str_ireplace('-', ' ', Str::kebab(Str::studly($modelName))));
        $pluralWords = ucwords(str_ireplace('-', ' ', $this->modelPluralKebabName($modelName)));
        // $pluralModel = $this->modelPluralName($modelName);
        $pluralModel = $this->modelPluralKebabName($modelName);
        d($pluralModel);
        if (!$this->disk()->exists($path)) {
            $this->components->twoColumnDetail("<fg=red>$path</> not exists", '<fg=red>Skipped</>');
        }
        else {
            $choiceContent = file($this->disk()->path($path));
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
            $this->disk()->put($path, $choiceFile);
            $this->components->twoColumnDetail($path, '<fg=green>Updated</>');
        }
    }

    /**
     * @return bool
     */
    protected function isForce(): bool
    {
        return $this->option('force');
    }

    /**
     * @return bool
     */
    protected function isDeleteMode(): bool
    {
        return $this->option('delete');
    }

    /**
     * @return bool
     */
    protected function isGeneric(): bool
    {
        return $this->option('generic');
    }

    /**
     * @return bool
     */
    protected function isScoped(): bool
    {
        return $this->option('scoped');
    }

    /**
     * @return bool
     */
    protected function isTranslator(): bool
    {
        return $this->option('translator');
    }
}

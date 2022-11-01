<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
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
    protected $signature = 'myth:model {model}
{--s|scoped : Create model with scopes}
{--d|delete : Delete model}';
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
     * @var string
     */
    protected string $model;

    /**
     * @var string
     */
    protected string $modelName;

    /**
     * @var string|null
     */
    protected ?string $namespace = null;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $this->prepare();
        $time = now()->format('Y_m_d');
        $snake = Str::snake(Str::pluralStudly($this->modelName));
        $stubs = [
            'ModelMigration.stub'     => "database/migrations/{$time}_000000_{$snake}_table.php",
            'ModelClass.stub'         => "app/Models/$this->model.php",
            'ModelController.stub'    => "app/Http/Controllers/{$this->model}Controller.php",
            'ModelResource.stub'      => "app/Http/Resources/{$this->model}Resource.php",
            'BelongsToModel.stub'     => "app/Traits/BelongsTo/BelongsTo{$this->modelName}.php",
            'BelongsToManyModel.stub' => "app/Traits/BelongsToMany/BelongsToMany{$this->modelName}.php",
            'HasManyModel.stub'       => "app/Traits/HasMany/HasMany{$this->modelName}.php",
        ];

        $deleteModel = (bool) $this->option('delete');
        if ($deleteModel && !$this->confirm("Delete <fg=red>$this->model</> ?")) {
            return 0;
        }

        $stubsPath = __DIR__.'/../../Stubs';
        foreach ($stubs as $stub => $path) {
            if ($deleteModel) {
                $this->components->task("Deleting <fg=red>$path</>", fn() => $this->disk()->delete($path));
                continue;
            }
            $content = $this->fillStub(file_get_contents("$stubsPath/$stub"));
            if ($this->disk()->exists($path)) {
                $this->components->warn("File exists: <fg=red>$path</> <fg=yellow;bg=black>Skipped</>");
                continue;
            }
            $this->components->task("Creating $path", fn() => $this->disk()->put($path, $content));
        }
        if ($deleteModel) {
            return 0;
        }

        $path = 'app\Providers\RouteServiceProvider.php';
        $existsNeedles = "$this->modelName::";
        $replaceContent = '        $this->binder(\''.$this->modelName.'\', \\App\\Models\\'.$this->model.'::class);';
        $this->modifyFile($path, $existsNeedles, $replaceContent);

        $path = 'app\Http\Controllers\SideMenuController.php';
        $existsNeedles = "// # $this->modelName.";
        $routeName = $this->modelPluralKebabName();
        $permissions = "$this->modelName.index";
        if ($this->namespace) {
            $routeName = strtolower(str_ireplace('\\', '.', $this->namespace)).".$routeName";
            $permissions = str_ireplace('\\', '.', $this->namespace).".$permissions";
        }
        $permissions = "'$permissions'";

        $replaceContent = <<<html
            $existsNeedles
            [
                'title'       => trans_choice("choice.{$this->modelPluralName()}", 2),
                'name'        => 'panel.$routeName',
                'icon'        => '',
                'permissions' => [$permissions],
            ],
html;
        $this->modifyFile($path, $existsNeedles, $replaceContent);
        $this->insertModelLanguage();
        $this->newLine();
        $modelNamespace = "\\App\\Http\\Controllers\\{$this->model}Controller";
        $this->components->info("Please insert model routes <fg=yellow;bg=black>apiResource('$this->modelName', $modelNamespace::class);</>");
        $this->components->info("Please run <fg=yellow;bg=black>php artisan setup:permissions</> to make permissions or add them manually.");
        return 1;
    }

    /**
     * Prepare model and namespace of model
     *
     * @return void
     */
    protected function prepare(): void
    {
        $this->model = preg_replace(['/\/+/', '/\\\+/'], '\\', $this->argument('model'));
        $options = explode('\\', $this->model);
        $this->modelName = array_pop($options);
        if (count($options) > 0) {
            $this->namespace = implode('\\', $options);
        }
    }

    /**
     * Fill stub content
     *
     * @param  string  $stub
     *
     * @return string
     */
    protected function fillStub(string $stub): string
    {
        $scoped = $this->option('scoped');
        $model_use = $class_use = $fillable = $attributes = $casts = $rules = $migration = $resource = $oldest = '';

        if ($scoped) {
            $model_use .= 'use Myth\LaravelTools\Traits\Utilities\OrderByScopeTrait;
use Myth\LaravelTools\Traits\Utilities\ActiveScopeTrait;
';
            $class_use .= 'use ActiveScopeTrait, OrderByScopeTrait;
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
            "active_to_string" => (string) $model->active_to_string,';
            $oldest .= '
        $this->oldest = \'order_by\';';

        }
        return str_ireplace([
            '{namespace}',
            '{model}',
            '{modelName}',
            '{year}',
            '{model_use}',
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
        ], [
            $this->namespace ? '\\'.$this->namespace : null,
            $this->model,
            $this->modelName,
            Carbon::now()->format('Y'),
            $model_use,
            $class_use,
            $fillable,
            $attributes,
            $casts,
            $rules,
            $migration,
            $resource,
            $oldest,
            $this->modelForeignKey(),
            $this->modelCamelName(),
            Str::camel($this->modelPluralName()),
        ], $stub);
    }

    /**
     * @return string
     */
    protected function modelForeignKey(): string
    {
        return Str::snake($this->modelName).'_id';
    }

    /**
     * @return string
     */
    protected function modelCamelName(): string
    {
        return Str::camel($this->modelName);
    }

    /**
     * @return string
     */
    protected function modelPluralName(): string
    {
        return Str::plural($this->modelName);
    }

    /**
     * @param $path
     * @param $existsNeedles
     * @param $replaceContent
     *
     * @return void
     */
    protected function modifyFile($path, $existsNeedles = null, $replaceContent = null): void
    {
        $this->components->task("Try to modify <fg=green>$path</>", function () use ($path, $existsNeedles, $replaceContent) {
            $comment = static::LINE_COMMENT_UPDATE;
            $this->newLine();

            // Get Source
            $source = file($this->disk()->path($path));
            $commentIndex = null;
            $existsLine = null;
            foreach ($source as $k => $line) {
                is_null($existsLine) && ($existsLine = Str::contains($line, $existsNeedles) ? $k : null);
                is_null($commentIndex) && ($commentIndex = Str::contains($line, $comment) ? $k : null);
            }

            if (!is_null($existsLine)) {
                ++$existsLine;
                $this->components->warn("<fg=red>$this->modelName</> found in line: {$existsLine}");
            }
            elseif (is_null($commentIndex)) {
                $this->components->info("Add this comment [$comment] to automatically modify the file or add this line: [$replaceContent]");
            }
            else {
                $before = array_slice($source, 0, $commentIndex + 1);
                $after = array_slice($source, $commentIndex + 1);
                $file = array_merge($before, [
                    $replaceContent,
                    PHP_EOL,
                ], $after);
                $this->disk()->put($path, implode('', $file));
                $this->components->twoColumnDetail("<fg=green>$path</>", '<fg=green>Updated</>');
            }
        });
        $this->newLine();
    }

    /**
     * @return string
     */
    protected function modelPluralKebabName(): string
    {
        return Str::kebab($this->modelPluralName());
    }

    /**
     * @return void
     */
    protected function insertModelLanguage(): void
    {
        $this->components->task("Add model language", function () {
            $pluralChoice = $this->modelPluralName();
            $this->newLine();
            $studlyWords = ucwords(str_ireplace('-', ' ', Str::kebab(Str::studly($this->modelName))));
            $pluralWords = ucwords(str_ireplace('-', ' ', $this->modelPluralKebabName()));
            foreach (config('4myth-tools.locales') as $locale) {
                $choice = "lang/$locale/choice.php";
                if (!$this->disk()->exists($choice)) {
                    $this->components->twoColumnDetail("<fg=red>$choice</> not exists", '<fg=red>Skipped</>');
                }
                elseif (trans_has("choice.$pluralChoice")) {
                    $this->components->twoColumnDetail("<fg=red>$pluralChoice</> Trans choice exists", '<fg=red>Skipped</>');
                }
                else {
                    $file = file($this->disk()->path($choice));
                    $lastCount = count($file) - 1;
                    $row = '    \''.$pluralChoice.'\' => \'';
                    if ($locale == 'ar') {
                        $row .= 'جمع|مفرد';
                    }
                    else {
                        $row .= "$studlyWords|$pluralWords";
                    }
                    $row .= '\','.PHP_EOL;
                    $last = $file[$lastCount];
                    $file[$lastCount] = $row;
                    $file[] = $last;
                    $this->disk()->put($choice, implode('', $file));
                    $this->components->twoColumnDetail($choice, '<fg=green>Updated</>');
                }
                $attribute = "lang/$locale/attributes.php";
                $attr = $this->modelForeignKey();
                $attrs = Str::plural(Str::beforeLast($attr, '_id')).'_id';
                if (!$this->disk()->exists($attribute)) {
                    $this->components->twoColumnDetail("<fg=red>$attribute</> not exists", '<fg=red>Skipped</>');
                    return;
                }

                $file = file($this->disk()->path($attribute));
                $last = array_pop($file);
                $updated = !1;
                if (!trans_has("attributes.$attr")) {
                    $updated = !0;
                    $file[] = "    '$attr' => '$studlyWords',".PHP_EOL;
                }
                else {
                    $this->components->twoColumnDetail("<fg=red>$attr</> attribute exists", '<fg=red>Skipped</>');
                }

                if (!trans_has("attributes.$attrs")) {
                    $updated = !0;
                    $file[] = "    '$attrs' => '$pluralWords',".PHP_EOL;
                }
                else {
                    $this->components->twoColumnDetail("<fg=red>$attrs</> attribute exists", '<fg=red>Skipped</>');
                }

                if ($updated) {
                    $file[] = $last;
                    $this->disk()->put($attribute, implode('', $file));
                    $this->components->twoColumnDetail($attribute, '<fg=green>Updated</>');
                }
            }
        });
    }

}

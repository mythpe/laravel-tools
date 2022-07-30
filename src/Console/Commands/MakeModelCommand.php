<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Console\Commands;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Myth\LaravelTools\Console\BaseCommand;

class MakeModelCommand extends BaseCommand
{

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        //if ($this->option('help')) {
        //    //$this->in
        //    $this->line("This command make a crud of model.");
        //    $this->line("to insert code automatically, pleas add this comment to files. [".static::LINE_COMMENT_UPDATE."]");
        //    $this->line('App\Providers\RouteServiceProvider.php');
        //    $this->line('App\Http\Controllers\SideMenuController.php');
        //    return 0;
        //}
        $disk = Storage::disk('root');
        $model = str_ireplace('/', '\\', ucwords(Str::studly($this->argument('model'))));
        $modelName = Str::afterLast(ucfirst($model), '\\');
        $namespace = collect(explode('\\', Str::beforeLast(ucfirst($model), '\\')))->map(fn($e) => ucfirst($e))->implode('\\');
        $time = now()->format('Y_m_d');
        $snake = Str::snake($modelName);
        $scoped = $this->option('scoped') ? 'Scoped' : '';
        $stubs = [
            $scoped.'ModelMigration.stub'  => "database/migrations/{$time}_000000_{$snake}_table.php",
            $scoped.'ModelClass.stub'      => "app/Models/$namespace/$modelName.php",
            $scoped.'ModelController.stub' => "app/Http/Controllers/$namespace/{$modelName}Controller.php",
            $scoped.'ModelResource.stub'   => "app/Http/Resources/$namespace/{$modelName}Resource.php",
        ];

        $deleteModel = (bool) $this->option('delete');
        if ($deleteModel && !$this->confirm("Delete $model ?")) {
            return 0;
        }
        $stubsPath = __DIR__.'/../../Stubs';
        foreach ($stubs as $stub => $path) {
            if ($deleteModel) {
                $this->components->task("Deleting [$path]", fn() => $disk->delete($path));
                continue;
            }
            $content = str_ireplace([
                '{namespace}',
                '{model}',
                '{year}',
            ], [
                $namespace,
                $modelName,
                Carbon::now()->format('Y'),
            ], file_get_contents("$stubsPath/$stub"));
            if ($disk->exists($path)) {
                $this->components->warn("File exists: [$path] <fg=yellow;bg=white>Skipped</>");
                //$this->error('Skipped');
                continue;
            }
            $this->components->task("Creating [$path]", fn() => $disk->put($path, $content));
        }
        if ($deleteModel) {
            return 0;
        }

        $pluralChoice = Str::plural($modelName);
        $kebabName = Str::kebab($pluralChoice);

        $this->components->task("Try to modify <fg=green>App\Providers\RouteServiceProvider.php</>", function () use ($disk, $modelName, $namespace) {
            $this->newLine();
            $path = 'App\Providers\RouteServiceProvider.php';
            $provider = file($disk->path($path));
            $commentIndex = null;
            $comment = static::LINE_COMMENT_UPDATE;
            $existsModelInFile = null;
            foreach ($provider as $k => $line) {
                is_null($existsModelInFile) && ($existsModelInFile = Str::contains($line, "$modelName::") ? $k : null);
                is_null($commentIndex) && ($commentIndex = Str::contains($line, $comment) ? $k : null);
            }
            $_line = '      $this->binder(\''.$modelName.'\', \\App\\Models\\'.$namespace.'\\'.$modelName.'::class);';
            if (!is_null($existsModelInFile)) {
                ++$existsModelInFile;
                //$this->components->twoColumnDetail("[$modelName] found in provider line: {$existsModelInFile}", '<fg=red>Skipped</>');
                $this->components->warn("[$modelName] found in line: {$existsModelInFile}");
            }
            elseif (is_null($commentIndex)) {
                $this->components->info("Add this comment [$comment] to automatically modify the file or add this line: [$_line]");
            }
            else {
                $before = array_slice($provider, 0, $commentIndex + 1);
                $after = array_slice($provider, $commentIndex + 1);
                $file = array_merge($before, [
                    $_line,
                    PHP_EOL,
                ], $after);
                $disk->put($path, implode('', $file));
                $this->components->twoColumnDetail("<fg=green>$path</>", '<fg=green>Updated</>');
            }
        });

        $this->components->task("Try to modify <fg=green>App\Http\Controllers\SideMenuController.php</>", function () use ($disk, $modelName, $namespace) {
            $this->newLine();
            $path = 'App\Http\Controllers\SideMenuController.php';
            $diskFile = file($disk->path($path));
            $commentIndex = null;
            $comment = static::LINE_COMMENT_UPDATE;
            $existsModelInFile = null;
            foreach ($diskFile as $k => $line) {
                is_null($existsModelInFile) && ($existsModelInFile = Str::contains($line, "// # $modelName") ? $k : null);
                is_null($commentIndex) && ($commentIndex = Str::contains($line, $comment) ? $k : null);
            }
            $pluralChoice = Str::plural($modelName);
            $kebabName = Str::kebab($pluralChoice);
            $permissions = str_ireplace(['\\', '/'], '.', $namespace);
            $_line = <<<html
            // # $modelName
            [
                'title'       => trans_choice("choice_custom.$pluralChoice", 2),
                'name'        => 'panel.$kebabName',
                'icon'        => '',
                'permissions' => ['$permissions.$modelName.index'],
            ],
html;
            if (!is_null($existsModelInFile)) {
                ++$existsModelInFile;
                //$this->components->twoColumnDetail("[$modelName] found in line: {$existsModelInFile}", '<fg=red>Skipped</>');
                $this->components->warn("[$modelName] found in line: {$existsModelInFile}");
            }
            elseif (is_null($commentIndex)) {
                $this->components->info("Add this comment [$comment] to automatically modify the file or add this line: [$_line]");
            }
            else {
                $before = array_slice($diskFile, 0, $commentIndex + 1);
                $after = array_slice($diskFile, $commentIndex + 1);
                $file = array_merge($before, [
                    $_line,
                    PHP_EOL,
                ], $after);
                $disk->put($path, implode('', $file));
                $this->components->twoColumnDetail("<fg=green>$path</>", '<fg=green>Updated</>');
            }
        });

        $this->components->task("Add model language", function () use ($disk, $pluralChoice, $modelName, $namespace, $kebabName) {
            $this->newLine();
            $studlyWords = ucwords(str_ireplace('-', ' ', Str::kebab(Str::studly($modelName))));
            foreach (config('4myth-tools.locales') as $locale) {
                $choice = "lang/$locale/choice_custom.php";
                if (!$disk->exists($choice)) {
                    $this->components->twoColumnDetail("$choice not exists", '<fg=red>Skipped</>');
                }
                elseif (trans_has("choice_custom.$pluralChoice") || trans_has("choice.$pluralChoice")) {
                    $this->components->twoColumnDetail("[$pluralChoice] Trans choice exists", '<fg=red>Skipped</>');
                }
                else {
                    $file = file($disk->path($choice));
                    $lastCount = count($file) - 1;
                    $row = '    \''.$pluralChoice.'\' => \'';
                    if ($locale == 'ar') {
                        $row .= 'جمع|مفرد';
                    }
                    else {
                        $pluralWords = ucwords(str_ireplace('-', ' ', $kebabName));
                        $row .= "$studlyWords|$pluralWords";
                    }
                    $row .= '\','.PHP_EOL;
                    $last = $file[$lastCount];
                    $file[$lastCount] = $row;
                    $file[] = $last;
                    $disk->put($choice, implode('', $file));
                    $this->components->twoColumnDetail($choice, '<fg=green>Updated</>');
                }
                $attribute = "lang/$locale/attributes_custom.php";
                $a = app("App\\Models\\{$namespace}\\{$modelName}");
                $attr = $a->getForeignKey();
                if (!$disk->exists($attribute)) {
                    $this->components->twoColumnDetail("$attribute not exists", '<fg=red>Skipped</>');
                }
                elseif (trans_has("attributes.$attr") || trans_has("attributes_custom.$attr")) {
                    $this->components->twoColumnDetail("[$attr] attribute exists", '<fg=red>Skipped</>');
                }
                else {
                    $file = file($disk->path($attribute));
                    $lastCount = count($file) - 1;
                    $last = $file[$lastCount];
                    $file[$lastCount] = "    '$attr' => '$studlyWords',".PHP_EOL;
                    $file[] = $last;
                    $disk->put($attribute, implode('', $file));
                    $this->components->twoColumnDetail($attribute, '<fg=green>Updated</>');
                    //dd($file);
                }
            }
        });
        $this->components->info("Please run [php artisan setup:permissions] to make permissions or add them manually.");
        return 1;
    }
}

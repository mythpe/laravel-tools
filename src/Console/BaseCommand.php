<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Console\View\Components\Factory;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Myth\LaravelTools\Console\Traits\CommandColors;
use Myth\LaravelTools\Console\Traits\ProgressBarTrait;
use Myth\LaravelTools\Models\BaseModel;

class BaseCommand extends Command
{
    use ProgressBarTrait, CommandColors;

    static bool $debug = !1;
    /** @var int - sleep seconds if error. */
    static int $ERROR_SLEEP_TIMEOUT = 1;
    /**
     * @var bool
     */
    protected bool $truncate = true;
    /**
     * @var bool
     */
    protected bool $echo = true;
    /**
     * @var Collection
     */
    protected Collection $collection;
    /**
     * @var array
     */
    protected array $tables = [];
    /**
     * @var string
     */
    protected string $diskName = 'setup';

    /**
     * Command constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->collection = Collection::make();
    }

    /**
     * Insert random image
     * @param BaseModel $model
     * @param array $data
     * @return void
     */
    public function insertImage(BaseModel $model, array $data): void
    {
        $keys = array_keys($data);
        $files = [];
        foreach ($keys as $key) {
            if ($key == '_files') {
                $files = [...$files, ...$data[$key]];
            }
            elseif ($key == '_file') {
                $files[] = $data[$key];
            }
        }
        foreach ($files as $file) {
            try {
                $src = $file;
                $collection = null;
                $single = is_array($src) ? ($src['single'] ?? !1) : !0;
                if (is_array($src)) {
                    $collection = $src['collection'] ?? null;
                    $src = $src['src'] ?? null;
                }
                if ($single && $collection) {
                    $model->clearMediaCollection($collection);
                }
                if (empty($src)) {
                    $this->components->error("Insert Image: [".get_class($model)."] ID => $model->id");
                    sleep(static::$ERROR_SLEEP_TIMEOUT);
                    continue;
                }
                $random = rand(1, 50);
                if ($src === 1 || $src === !0) {
                    $r = 2 / 3;
                    $w = 400;
                    $h = (int) floor($w * $r);
                    $src = "https://picsum.photos/id/$random/$w/$h";
                }
                elseif (is_array($src)) {
                    $src = "https://picsum.photos/id/$random/$src[0]/".($src[1] ?? $src[0]);
                }
                elseif (Str::startsWith($src, ($r = 'r:'))) {
                    $array = explode(',', Str::after($src, $r));
                    $r = explode('/', $array[0]);
                    $r = $r[0] / $r[1];
                    $w = $array[1];
                    $h = (int) floor($w * $r);
                    $src = "https://picsum.photos/id/$random/$w/$h";
                }
                elseif (is_string($src)) {
                    $src = filter_var($src, FILTER_VALIDATE_URL) ? $src : (is_file($src) ? $src : (Str::startsWith($src, '/') ? base_path($src) : $this->disk()->path($src)));
                }
                $props = is_array($src) ? ($src['props'] ?? []) : [];
                $model->addModelMedia($src, $collection, $props);
            }
            catch (Exception $exception) {
                $this->components->error("Insert Image: [".get_class($model)."] ID => $model->id");
                $this->components->error($exception);
                sleep(static::$ERROR_SLEEP_TIMEOUT);
            }
        }
    }

    /**
     * @return Factory
     */
    public function getComponents(): Factory
    {
        return $this->components;
    }

    /**
     * @param array|string $data - Get data if it is a file or array.
     * @return array
     */
    public function getRowData(array | string $data): array
    {
        if (!is_array($data)) {
            $path = is_file($data) ? $data : $this->disk()->path($data);
            $data = Str::endsWith($data, '.json') ? json_decode(file_get_contents($path), !0) : require($path);
        }
        return $data;
    }

    /**
     * @param $directory - All files in this directory.
     * File name must end with .php or .json.
     * To ignore files start with '_' or '.' or '.ignored'
     * @param bool $file - Do insert from file.
     *
     * @throws FileNotFoundException
     */
    protected function fetchFiles($directory, bool $file = !1): void
    {
        // $this->newLine();
        $this->iniCollection();
        Schema::disableForeignKeyConstraints();
        $files = $file ? [$directory] : $this->disk()->files($directory);
        $files = collect($files)->filter(fn(string $file) => Str::endsWith($file, [
                '.php',
                '.json',
            ]) && !Str::startsWith(pathinfo($file, PATHINFO_FILENAME), [
                '_',
                '.',
                '.ignored',
            ]))->sort()->values();
        $this->startBar($files);
        foreach ($files as $file) {
            $row = $this->getRowData($file);
            $name = Str::afterLast($file, '-');
            $table = Str::of(pathinfo($name, PATHINFO_FILENAME))->snake()->plural()->lower();
            $this->truncate($table);
            foreach ($row as $data) {
                $this->insert($data, $table);
            }
        }
        $this->finishBar();
    }

    /**
     * @param array $data
     *
     * @return Collection
     */
    protected function iniCollection(array $data = []): Collection
    {
        if (!$this->collection instanceof Collection) {
            $this->collection = Collection::make($data);
        }
        return $this->collection;
    }

    /**
     * @return Filesystem
     */
    protected function disk(): Filesystem
    {
        return Storage::disk($this->diskName);
    }

    /**
     * @param $table
     */
    protected function truncate($table): void
    {
        if (!$this->truncate) {
            return;
        }
        $originalTable = $table;
        if ($this->isTruncated($table)) {
            return;
        }

        if (!Schema::hasTable($table)) {
            $found = false;
            foreach (['snake', 'camel', 'kebab'] as $method) {
                $table = Str::{$method}(Str::plural($table));
                if (Schema::hasTable($table)) {
                    $this->doTruncate($table);
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                if (Schema::hasTable(($t = 'c_'.Str::snake(Str::plural($originalTable))))) {
                    $this->doTruncate($t);
                }
                else {
                    $this->components->error("Table: {$originalTable}. not found");
                    sleep(static::$ERROR_SLEEP_TIMEOUT);
                }
            }
        }
        else {
            $this->doTruncate($table);
        }
    }

    /**
     * @param $table
     *
     * @return bool
     */
    protected function isTruncated($table): bool
    {
        return in_array($table, $this->tables);
    }

    /**
     * @param $table
     */
    protected function doTruncate($table): void
    {
        if ($this->isTruncated($table)) {
            return;
        }
        $this->tables[] = $table;
        DB::table($table)->truncate();
        // $this->table(['name'], collect($this->tables)->map(fn($t) => ['name' => $t]));
    }

    /**
     * @param array|string $data - The data will insert. Array or string file path.
     * @param $tableOrRelation - The table name.
     * @param $RelationModel - Model instance.
     * @return void
     */
    protected function insert(array | string $data, string $tableOrRelation, ?Relation $RelationModel = null): void
    {
        $this->iniCollection();
        $data = $this->getRowData($data);
        $hasRelations = array_key_exists('_data', $data);
        $insert = $hasRelations ? $data['_data'] : $data;
        request()->merge($insert);
        if ($hasRelations) {
            unset($data['_data']);
        };
        $parentName = $RelationModel ? class_basename($RelationModel) : null;
        if (is_null($RelationModel)) {
            $namespaces = ['\\App\\Models', '\\App\\Models\\Utilities'];
            $directories = Storage::disk('app')->directories('Models');
            foreach ($directories as $directory) {
                $namespaces[] = '\\App\\'.str_ireplace('/', '\\', $directory);
            }
            $class = ucfirst(Str::camel(Str::singular($tableOrRelation)));
            $model = null;
            foreach ($namespaces as $namespace) {
                $c = "{$namespace}\\{$class}";
                if (class_exists($c)) {
                    $model = $c;
                    break;
                }
            }
            /** @var BaseModel $model */
            $model = $model::create(Arr::only($insert, (new $model())->getFillable()));
        }
        else {
            // $cases = [$tableOrRelation, Str::snake($tableOrRelation), Str::camel($tableOrRelation), Str::studly($tableOrRelation)];
            // $model = null;
            // $found = !1;
            // foreach ($cases as $case) {
            //     if (method_exists($RelationModel, $case)) {
            //         $model = $RelationModel->{$case}();
            //         // $found = !0;
            //         break;
            //     }
            // }
            // if (!$model) {
            //     $this->components->error("Model: {$tableOrRelation} not found.");
            //     return;
            // }
            // if ($parent instanceof Relation) {
            //     $fill = Arr::only($insert, $parent->getModel()->getFillable());
            // }
            // else {
            //     $fill = Arr::only($insert, $parent->getFillable());
            // }
            $fill = Arr::only($insert, $RelationModel->getModel()->getFillable());
            $model = $RelationModel->create($fill);
        }
        if ($model->isFillable('order_by') && !$model->order_by) {
            $model->update(['order_by' => $model::query()->count() + 1]);
        }
        $this->insertImage($model, $insert);
        $this->pushData($model);
        // $classLabel = Str::singular(class_basename($model));
        // $this->echo("[".($parentName ? "$parentName => " : '')."$classLabel] => {$model->id}");
        if ($hasRelations && count($data) > 0) {
            $this->startBar($data);
            foreach ($data as $relationName => $row) {
                if (Str::startsWith($relationName, '_')) {
                    continue;
                }
                $row = $this->getRowData($row);
                $relation = $model->{$relationName}();
                $table = method_exists($relation, 'getTable') ? $relation->getTable() : $relationName;
                $this->truncate($table);
                if ($relation instanceof BelongsToMany) {
                    if (!is_array($row[0] ?? null)) {
                        $relation->sync($row, !1);
                        // $this->echo("Sync [$classLabel]: ".json_encode($row));
                        continue;
                    }
                    else {
                        $this->truncate($relation->getModel()->getTable());
                    }
                }
                foreach ($row as $rowData) {
                    $this->insert($rowData, $relationName, $relation);
                }
            }
        }
    }

    /**
     * @param string $text
     * @param string|null $method
     * @return void
     */
    protected function echo(string $text, ?string $method = null): void
    {
        if (app()->runningInConsole()) {
            if (!$method || !method_exists($this, $method)) {
                // $this->l($text, $method);
                $this->components->info($text);
            }
            else {
                $this->{$method}($text);
            }
        }
        else {
            if (!$this->echo) {
                return;
            }
            echo "{$text}<BR>";
        }
    }

    /**
     * @param $model
     *
     * @return Collection
     */
    protected function pushData($model): Collection
    {
        $key = is_object($model) ? get_class($model) : $model;
        $this->iniCollection();
        if (!$this->collection->has($key)) {
            $this->collection->put($key, Collection::make());
        }
        /** @var Collection $data */
        $data = $this->collection->get($key);
        $data->push($model);
        $this->collection->put($key, $data);
        $this->advanceBar();
        return $this->collection;
    }

    /**
     * @param $class
     *
     * @return Collection|null
     */
    protected function getCollection($class): ?Collection
    {
        return $this->collection->get($class);
    }

    /**
     * Check if command has truncate option. {--t|truncate : Truncate table}
     *
     * @return bool
     */
    protected function isTruncateOption(): bool
    {
        return (bool) $this->option('truncate');
    }

    /**
     * @param $name
     *
     * @return string
     */
    protected function parseFunctionName($name): string
    {
        $n = Str::before($name, '{');
        $n = ucfirst(str_ireplace(['-', ':'], ' ', strtolower(Str::kebab($n))));
        return Str::pluralStudly($n);
    }
}

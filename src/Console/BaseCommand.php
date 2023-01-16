<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Console;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Myth\LaravelTools\Console\Traits\CommandColors;
use Myth\LaravelTools\Console\Traits\ProgressBarTrait;

class BaseCommand extends Command
{
    use ProgressBarTrait, CommandColors;

    public bool $debug = false;
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
     * @param $directory
     * @param  bool  $file
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function fetchFiles($directory, bool $file = !1)
    {
        $this->iniCollection();
        Schema::disableForeignKeyConstraints();

        $files = $file ? [$directory] : $this->disk()->files($directory);

        asort($files);
        //d($files);
        foreach ($files as $file) {
            if (!Str::endsWith($file, '.json')) {
                continue;
            }
            //if($file != 'utilities/countries.json'){
            //    continue;
            //}
            //d($file);
            $data = json_decode($this->disk()->get($file), true);
            $table = strtolower(Str::snake(Str::plural(pathinfo($file, PATHINFO_FILENAME))));
            $this->truncate($table);
            //d($data);
            foreach ($data as $v) {
                $this->insert($v, $table);
            }
        }
    }

    /**
     * @param  array  $data
     *
     * @return \Illuminate\Support\Collection
     */
    protected function iniCollection(array $data = []): Collection
    {
        if (!$this->collection instanceof Collection) {
            $this->collection = Collection::make($data);
        }
        return $this->collection;
    }

    /**
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function disk(): Filesystem
    {
        return Storage::disk($this->diskName);
    }

    /**
     * @param $table
     */
    protected function truncate($table)
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
    protected function doTruncate($table)
    {
        if ($this->isTruncated($table)) {
            return;
        }
        !$this->debug && $this->components->info("truncated : {$table}");
        $this->tables[] = $table;
        DB::table($table)->truncate();
    }

    /**
     * @param $data
     * @param $table
     * @param  null  $model
     */
    protected function insert($data, $table, $model = null)
    {
        $this->iniCollection();
        //d($data);
        $hasRelations = array_key_exists('data', $data);
        $insert = $hasRelations ? $data['data'] : $data;
        unset($data['data']);
        //d($data);

        if (is_null($model)) {
            $namespaces = ['\\App\\Models', '\\App\\Models\\Utilities'];
            $directories = Storage::disk('app')->directories('Models');
            foreach ($directories as $directory) {
                $namespaces[] = '\\App\\'.str_ireplace('/', '\\', $directory);
            }
            $class = ucfirst(Str::camel(Str::singular($table)));
            $model = null;
            foreach ($namespaces as $namespace) {
                $c = "{$namespace}\\{$class}";
                if (class_exists($c)) {
                    $model = $c;
                    break;
                }
            }
            $model = new $model();
            $insert = Arr::only($insert, $model->getFillable());
            $model->fill($insert);
            $model->save();
            $this->pushData($model);
        }
        else {
            $relationType = $model->{$table}();
            if (
                !$relationType instanceof HasMany
                // !$relationType instanceof BelongsToMany
            ) {
                d(get_class($model->{$table}()));
            }
            // d($insert,$model,$model->getFillable());
            // $insert = Arr::only($insert, $model->getFillable());
            $model = $model->{$table}()->create($insert);
            $this->pushData($model);
        }
        !$this->debug && $this->echo(class_basename($model)." Inserted: {$model->id}");

        if ($hasRelations && count($data) > 0) {
            //d($data);
            foreach ($data as $relation => $row) {
                if (Str::startsWith($relation, '_')) {
                    continue;
                }
                $this->truncate($relation);
                foreach ($row as $child) {
                    $this->insert($child, $relation, $model);
                }
            }
        }
    }

    /**
     * @param $model
     *
     * @return \Illuminate\Support\Collection
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
        return $this->collection;
    }

    /**
     * @param  string  $text
     * @param  string  $method
     */
    protected function echo(string $text = '', string $method = 'line')
    {
        if (app()->runningInConsole()) {
            if (!method_exists($this, $method)) {
                $this->l($text, $method);
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
        $n = Str::pluralStudly($n);
        return $n;
    }
}

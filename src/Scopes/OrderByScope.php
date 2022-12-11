<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Schema;

/**
 * Class OrderByScope
 *
 * @package App\Scopes
 */
class OrderByScope implements Scope
{
    /** @var array<int|string,string> */
    public array $columns = ['order_by'];

    /**
     * @var string
     */
    public string $direction = "asc";

    /**
     * OrderByScope constructor.
     *
     * @param array|null $columns
     * @param string|null $direction
     */
    public function __construct(array $columns = null, string $direction = null)
    {
        $this->columns = $columns ?: $this->columns;
        $this->direction = $direction ?: $this->direction;
    }

    /**
     * @return static
     */
    public static function make()
    {
        return new static(...func_get_args());
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param \Illuminate\Database\Eloquent\Model $model
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function apply(Builder $builder, Model $model)
    {
        $table = $model->getTable();
        foreach($this->columns as $k => $column){
            $direction = $this->direction;
            if(!is_numeric($k)){
                $direction = $column;
                $column = $k;
            }
            if(!$column || !Schema::hasColumn($table, $column)){
                continue;
            }
            //d($column,$direction);
            $builder->orderBy("{$table}.{$column}", $direction);
        }
        return $builder;
    }
}

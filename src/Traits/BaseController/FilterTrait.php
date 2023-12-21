<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Myth\LaravelTools\Models\BaseModel;
use Myth\LaravelTools\Utilities\Helpers;

trait FilterTrait
{
    /**
     * @var string
     */
    public string $filterRequestKey = 'filter';

    /**
     * user_id => "scope"
     * user_id => ["where",'name','LIKE','and']
     * start_date => ["whereDate",'created_at','>=','and']
     *
     * @var array
     */
    public array $mapFilterColumns = [];

    /**
     * @var string
     */
    protected string $filterTable = '';

    /**
     * @param Builder|BaseModel $builder
     * @param null $filters
     *
     * @return Builder
     */
    protected function filerQuery($builder, $filters = null)
    {
        $filters = is_null($filters) ? $this->request->input($this->filterRequestKey) : $filters;
        // d($filters);
        if ($filters && is_array($filters)) {
            $model = $builder->getModel();
            $this->filterTable = $model->getTable();
            foreach ($filters as $column => $value) {
                if (is_null($value)) {
                    continue;
                }
                if ($this->isMapFilterColumns($column)) {
                    $map = $this->getMapFilterColumns($column);
                    if (is_string($map)) {
                        $builder = $builder->{$map}($value);
                    }
                    else {
                        $method = ($map[0] ?? 'where');
                        $column = ($map[1] ?? $column);
                        $operator = ($map[2] ?? '=');
                        $boolean = ($map[3] ?? 'and');
                        $value = strtolower($operator) == 'like' ? "%{$value}%" : $value;
                        $builder = $builder->{$method}($column, $operator, $value, $boolean);
                    }
                }
                else {
                    $builder = $this->setFilterQuery($builder, $column, $value);
                }
            }
        }
        return $builder;
    }

    /**
     * @param $column
     *
     * @return bool
     */
    protected function isMapFilterColumns($column): bool
    {
        return array_key_exists($column, $this->getMapFilterColumns());
    }

    /**
     * @param null $column
     *
     * @return array|mixed|null
     */
    protected function getMapFilterColumns($column = null)
    {
        return is_null($column) ? $this->mapFilterColumns : (($this->mapFilterColumns[$column]) ?? null);
    }

    /**
     * @param Builder $builder
     * @param $column
     * @param $value
     *
     * @return mixed
     */
    protected function setFilterQuery($builder, $column, $value)
    {
        if (Schema::hasColumn($this->filterTable, $column)) {
            $model = $builder->getModel();
            if (is_array($value)) {
                /** @var Model $model */
                if (Helpers::hasDateCast($model, $column)) {
                    $from = Carbon::make(($value['form'] ?? ($value[0] ?? null)));
                    $to = Carbon::make(($value['to'] ?? ($value[1] ?? null)));
                    $builder->whereDate($column, '>=', $from->min($to))->whereDate($column, '<=', $to->max($from));
                }
                elseif (Helpers::hasNumericCast($model, $column) && !Str::endsWith($column, '_id')) {
                    $from = ($value['form'] ?? ($value[0] ?? null));
                    $to = ($value['to'] ?? ($value[1] ?? null));
                    $builder->where($column, '>=', min($from, $to))->where($column, '<=', max($to, $from));
                }
                else {
                    if (is_array($value[0] ?? null)) {
                        $found = null;
                        foreach (['id', 'value', 'key'] as $key) {
                            if (array_key_exists($key, $value[0])) {
                                $found = $key;
                                break;
                            }
                        }
                        if (!is_null($found)) {
                            $value = collect($value)->pluck($found)->toArray();
                        }
                    }
                    $builder->whereIn($column, $value);
                }
            }
            else {
                if (Helpers::hasDateCast($model, $column)) {
                    $builder->whereDate($column, $value);
                }
                else {
                    $builder->where($column, '=', $value);
                }
            }
        }
        else {
            $model = $builder->getModel();
            $name = Str::beforeLast($column, '_id');
            $camel = ucfirst(Str::camel($name));
            $method = "whereHas{$camel}";
            $scope = "scopeWhereHas{$camel}";
            if (method_exists($model, $scope)) {
                $builder->{$method}($value);
            }
            $relations = array_unique([
                Str::camel($name),
                Str::snake($name),
            ]);
            foreach ($relations as $relation) {
                if (method_exists($model, $relation)) {
                    $_relation = $model->{$relation}();
                    if ($_relation instanceof BelongsToMany) {
                        $builder->whereHas($relation, function (Builder $r) use ($value, $relation, $_relation) {
                            // $relationColumn = Str::singular(Str::snake($relation)).'_id';
                            $relationColumn = $_relation->getRelatedPivotKeyName();
                            $value = !is_array($value) && Str::contains($value, ',') ? explode(',', $value) : $value;
                            $m = is_array($value) ? 'whereIn' : 'where';
                            return $r->{$m}("{$_relation->getTable()}.{$relationColumn}", $value);
                        });
                        break;
                    }
                }
            }
        }
        return $builder;
    }
}

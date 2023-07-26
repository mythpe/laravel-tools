<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait SearchTrait
{
    /**
     * @var bool
     */
    public bool $customSearchColumns = false;

    /**
     * Fields to be searched
     *
     * @var array
     */
    public array $searchColumns = [];

    /**
     * user_id => ['user']
     * user_id => ['relation' => 'user', 'method' => 'whereHas', 'column' => 'name', 'operator' => 'LIKE', 'value' => '%{v}%']
     *
     * @var array<string,array>
     */
    public array $mapSearchQueryColumns = [];

    /**
     * @var string
     */
    public string $searchRequestKey = 'search';

    /**
     * @var string
     */
    public string $headersRequestKey = 'headerItems';

    /**
     * @var string
     */
    protected string $searchTable = '';

    /**
     * @param  \Illuminate\Database\Eloquent\Builder|\Myth\LaravelTools\Models\BaseModel  $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder|\Myth\LaravelTools\Models\BaseModel
     */
    protected function searchQuery($builder)
    {
        $words = $this->request->input($this->searchRequestKey);
        //d($words);
        if (!$words) {
            return $builder;
        }
        $model = $builder->getModel();
        $this->searchTable = $model->getTable();
        if (!$this->customSearchColumns) {
            //d($words, $model);
            if (($headers = $this->request->input($this->headersRequestKey)) && is_array($headers) && !empty($headers)) {
                // d($headers);
                foreach ($headers as $header) {
                    $insertNameColumns = !1;
                    if (is_array($header)) {
                        $column = ($header['value'] ?? ($header['field'] ?? ($header['name'] ?? null)));
                        if ($column == 'name' && !$model->isFillable($column) && $model->isFillable(locale_attribute("name"))) {
                            $column = locale_attribute("name");
                            $insertNameColumns = !0;
                        }
                        foreach (['_to_string', 'ToString', '_to_yes', 'ToYes', '_to_number_format', 'toNumberFormat'] as $c) {
                            if (Str::endsWith($column, $c)) {
                                $column = Str::beforeLast($column, $c);
                                break;
                            }
                        }
                    }
                    else {
                        $column = $header;
                    }
                    // d($column,$this->searchTable);
                    if ($column && Schema::hasColumn($this->searchTable, $column)) {
                        $this->mergeSearchColumns($column);
                        /*if($insertNameColumns && ($locales = config('4myth-tools.locales'))){
                            foreach ($locales as $l){
                                if($l == $column){
                                    continue;
                                }
                                if(Schema::hasColumn($this->searchTable, $l)){
                                    $this->mergeSearchColumns($column);
                                }
                            }

                        }*/
                    }
                }
            }
            else {
                $this->mergeSearchColumns($model->getFillable());
            }
            // d($this->getSearchColumns());
        }

        $builder->where(function (Builder $builder) use ($words, $model) {
            foreach ($this->getSearchColumns() as $k => $column) {
                /** Default no custom */
                if (is_numeric($k)) {
                    if (Schema::hasColumn($this->searchTable, $column) || $this->isMapQueryColumn($column)) {
                        if ($this->isMapQueryColumn($column)) {
                            $map = $this->getMapSearchQueryColumns($column);
                            if (count($map) == 1 && method_exists($model, 'scope'.ucfirst($map[0]))) {
                                //d($map,$words);
                                $builder->orWhere(fn($q) => $q->{$map[0]}($words));
                            }
                            else {
                                $relation = ($map['relation'] ?? ($map[0] ?? Str::beforeLast($column, '_id')));
                                $method = ($map['method'] ?? 'orWhereHas');
                                $operator = ($map['operator'] ?? 'LIKE');
                                $value = str_ireplace('{v}', $words, ($map['value'] ?? '%{v}%'));
                                $column = ($map['column'] ?? 'name');
                                // d($relation,$method,$operator,$value,$column);
                                $builder->{$method}($relation, function (Builder $builder) use ($column, $operator, $value, $words) {
                                    return $builder->where($column, $operator, $value);
                                });
                            }
                        }
                        else {

                            //if (Str::endsWith($column, '_id') && !is_numeric($words)) {
                            //    if (method_exists($model, ($relation = Str::beforeLast($column, '_id'))) && ($relationModel = $model->$relation()
                            //                                                                                                        ->getModel()) && Schema::hasColumn($relationModel->getTable(), ($c = $relationModel->getNameColumn()))) {
                            //        $builder->orWhere(function (Builder $builder) use ($relation, $c, $words) {
                            //            $builder->whereHas($relation, function (Builder $builder) use ($c, $words) {
                            //                $builder->where($c, 'LIKE', "%{$words}%");
                            //            });
                            //        });
                            //    }
                            //}

                            if (Str::endsWith($column, '_id') && !is_numeric($words)) {
                                // d($words,$column);
                                $str = Str::beforeLast($column, '_id');
                                $relations = array_unique([
                                    Str::snake($str),
                                    Str::camel($str),
                                    ucfirst(Str::camel($str)),
                                ]);
                                foreach ($relations as $relation) {
                                    if (
                                        method_exists($model, $relation)
                                        && ($relationModel = $model->$relation()->getModel())
                                        && method_exists($relationModel, 'getNameColumn')
                                        && Schema::hasColumn($relationModel->getTable(), ($c = $relationModel->getNameColumn()))
                                    ) {
                                        $builder->orWhere(function (Builder $builder) use ($relation, $c, $words) {
                                            $builder->whereHas($relation, function (Builder $builder) use ($c, $words) {
                                                $builder->where($c, 'LIKE', "%{$words}%");
                                            });
                                        });
                                    }
                                }

                                // d($words,$column,$relations);
                                // foreach($relations as $relation){
                                //     /** @var \Illuminate\Database\Eloquent\Model $relationModel */
                                //     if(
                                //         method_exists($model, $relation)
                                //         && ($relationModel = $model->$relation()->getModel())
                                //         && Schema::hasColumn($relationModel->getTable(), ($c = $relationModel->getNameColumn()))
                                //     ){
                                //         $builder->orWhere(fn(Builder $b) => $b->whereHas($relation, function(Builder $builder) use ($relationModel, $words){
                                //             $relationColumns = $relationModel->getFillable();
                                //             // d($relationColumns, $relationModel);
                                //             $relationColumn = null;
                                //             foreach($relationColumns as $relationColumn){
                                //                 // d($relationColumn, $relationModel);
                                //                 $builder->orWhere($relationColumn, 'LIKE', "%{$words}%");
                                //             }
                                //             return $builder;
                                //         }));
                                //     }
                                // }

                            }
                            elseif ($column == 'id' && is_numeric($words)) {
                                $builder->where($column, '=', (int) $words);
                            }
                            else {
                                $builder->orWhere($column, 'LIKE', "%{$words}%");
                            }
                        }
                    }
                }
                else {
                    //d($words);
                    $builder->orWhere(function (Builder $builder) use ($column, $words) {
                        return $builder->{$column}($words);
                    });
                }
            }
            // d($builder->toSql());
            return $builder;
        });
        //d($builder->toSql(), $words);

        return $builder;
    }

    /**
     * @param  string|array  $columns
     *
     * @return self
     */
    protected function mergeSearchColumns($columns): self
    {
        $columns = func_num_args() == 1 ? $columns : func_get_args();
        !is_array($columns) && ($columns = explode(',', $columns));
        $this->searchColumns = array_merge($this->searchColumns, $columns);
        return $this;
    }

    /**
     * @return array
     */
    protected function getSearchColumns(): array
    {
        return $this->searchColumns;
    }

    /**
     * @param $column
     *
     * @return bool
     */
    protected function isMapQueryColumn($column): bool
    {
        return array_key_exists($column, $this->mapSearchQueryColumns);
    }

    /**
     * @param  null  $column
     *
     * @return array
     */
    protected function getMapSearchQueryColumns($column = null): array
    {
        return is_null($column) ? $this->mapSearchQueryColumns : ($this->mapSearchQueryColumns[$column] ?? []);
    }
}

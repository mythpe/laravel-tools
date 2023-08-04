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
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Myth\LaravelTools\Models\BaseModel;

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
    public string $headersRequestKey = 'searchColumns';

    /**
     * @var string
     */
    protected string $searchTable = '';

    /**
     * @param Builder|BaseModel $builder
     *
     * @return Builder|BaseModel
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
        $nameAttribute = 'name';
        if (!$this->customSearchColumns) {
            // d($words, $model);
            // d(1,$this->request->input($this->headersRequestKey));
            if (($headers = $this->request->input($this->headersRequestKey)) && is_array($headers) && !empty($headers)) {
                // d($headers);
                foreach ($headers as $header) {
                    $insertNameColumns = !1;
                    if (is_array($header)) {
                        $column = ($header['value'] ?? ($header['name'] ?? ($header['field'] ?? ($header['label'] ?? null))));
                        if ($column == $nameAttribute && !$model->isFillable($column) && $model->isFillable(locale_attribute($nameAttribute))) {
                            $column = locale_attribute($nameAttribute);
                            $insertNameColumns = !0;
                        }
                        foreach (['_to_string', 'ToString', '_to_yes', 'ToYes', '_to_number_format', 'toNumberFormat'] as $c) {
                            if (Str::endsWith($column, $c)) {
                                $column = Str::beforeLast($column, $c);
                                break;
                            }
                        }
                    } else {
                        $column = $header;
                        if ($column == $nameAttribute && !$model->isFillable($column) && $model->isFillable(locale_attribute($nameAttribute))) {
                            $insertNameColumns = !0;
                        }
                    }
                    // d($nameAttribute,$column,$insertNameColumns);
                    if ($insertNameColumns && ($locales = config('4myth-tools.locales'))) {
                        foreach ($locales as $l) {
                            $newCol = "{$nameAttribute}_{$l}";
                            // d($newCol);
                            if ($newCol == $column) {
                                continue;
                            }
                            if (Schema::hasColumn($this->searchTable, $newCol)) {
                                $this->mergeSearchColumns($newCol);
                            }
                        }

                    }
                    if ($column) {
                        if (Schema::hasColumn($this->searchTable, $column)) {
                            $this->mergeSearchColumns($column);
                        } else {
                            if (!$this->isMapQueryColumn($column)) {
                                if (method_exists($model, $this->parseColumn($column))) {
                                    $this->mergeSearchColumns($column);
                                } elseif (method_exists($model, $this->parseColumn($column))) {
                                    $this->mergeSearchColumns($column);
                                }
                            }
                        }
                    }
                }
            } else {
                $this->mergeSearchColumns($model->getFillable());
            }
        }

        $builder->where(function (Builder $builder) use ($words, $model) {
            foreach ($this->getSearchColumns() as $k => $column) {
                /** Default no custom */
                if (is_numeric($k)) {
                    if (
                        // Normal
                        Schema::hasColumn($this->searchTable, $column)
                        // Set map from controller
                        || $this->isMapQueryColumn($column)
                        // Search if it has relation
                        || method_exists($model, $this->parseColumn($column))
                        || method_exists($model, $this->parseColumn($column, !0))
                    ) {
                        if ($this->isMapQueryColumn($column)) {
                            $map = $this->getMapSearchQueryColumns($column);
                            if (count($map) == 1 && method_exists($model, 'scope'.ucfirst($map[0]))) {
                                $builder->orWhere(fn($q) => $q->{$map[0]}($words));
                            } else {
                                // d(3);
                                $relation = ($map['relation'] ?? ($map[0] ?? Str::beforeLast($column, '_id')));
                                $method = ($map['method'] ?? 'orWhereHas');
                                $operator = ($map['operator'] ?? 'LIKE');
                                $value = str_ireplace('{v}', $words, ($map['value'] ?? '%{v}%'));
                                $column = ($map['column'] ?? null);
                                if (is_null($column)) {
                                    if (method_exists($model, 'getNameColumn')) {
                                        $column = $model->getNameColumn();
                                    } else {
                                        $name = 'name';
                                    }
                                }
                                // d($relation,$method,$operator,$value,$column);
                                $builder->{$method}($relation, function (Builder $builder) use ($column, $operator, $value, $words) {
                                    return $builder->where($column, $operator, $value);
                                });
                            }
                        } else {

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

                            } elseif ($column == 'id' && is_numeric($words)) {
                                $builder->where($column, '=', (int) $words);
                            } else {
                                $builder->orWhere($column, 'LIKE', "%{$words}%");
                            }
                        }
                    }
                } else {
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
     * @param string|array $columns
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
     * @param $column
     *
     * @return bool
     */
    protected function isMapQueryColumn($column): bool
    {
        return array_key_exists($column, $this->mapSearchQueryColumns);
    }

    public function parseColumn($str, $camel = !1): string
    {
        if (!$camel) {
            return Str::snake(Str::beforeLast($str, '_id'));
        }
        return Str::endsWith($str, 'Id') ? Str::camel(Str::beforeLast($str, 'Id')) : $str;
    }

    /**
     * @return array
     */
    protected function getSearchColumns(): array
    {
        return $this->searchColumns;
    }

    /**
     * @param null $column
     *
     * @return array
     */
    protected function getMapSearchQueryColumns($column = null): array
    {
        return is_null($column) ? $this->mapSearchQueryColumns : ($this->mapSearchQueryColumns[$column] ?? []);
    }
}

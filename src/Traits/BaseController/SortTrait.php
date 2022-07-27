<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait SortTrait
{
    /**
     * @var string
     */
    public string $sortByRequestKey = 'sortBy';

    /**
     * @var string
     */
    public string $sortDescRequestKey = 'sortDesc';

    /**
     * @var array
     */
    public array $mapSortColumns = [];

    /**
     * convert column type raw query
     * [column] => type
     *
     * @var array
     */
    public array $orderByRawColumns = [];

    /**
     * apply sort by scope
     * [column] => scope
     *
     * @var array
     */
    public array $orderByScopes = [];

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Relations\Relation|mixed $query
     *
     * @return mixed
     */
    protected function sortQuery($query)
    {
        /** @var array|string $sortBy */
        $sortBy = $this->request->get($this->sortByRequestKey);
        if($sortBy && !is_array($sortBy)){
            $sortBy = ($a = json_decode($sortBy, true)) ? $a : [];
        }

        /** @var array|string $sortDesc */
        $sortDesc = $this->request->get($this->sortDescRequestKey);
        if($sortDesc && !is_array($sortDesc)){
            $sortDesc = ($a = json_decode($sortDesc, true)) ? $a : [];
        }

        //$query->getQuery()->orders = [];
        //$query = ($query->getModel())->scopeAvailableOnly($query);
        //$query->addSelect([
        //    'views' => asf::selectRaw('sum(`views`) as total')
        //        ->whereColumn('re_id', 'users.id')
        //        ->groupBy('re_id'),
        //])
        //    ->orderBy('views', 'desc');
        //$query->withCount('housingUnits');
        //$query->withSum('housingUnits', 'views');
        //$query->orderBy('housing_units_count', 'desc');
        //$query->orderBy('views', 'desc');
        // d($sortBy,$sortDesc);
        $model = $query->getModel();
        $table = $model->getTable();
        if(
            is_array($sortBy)
            && !empty($sortBy)
            && is_array($sortDesc)
            && !empty($sortDesc)
        ){
            //d($sortBy,$sortDesc);
            $emptyBaseOrder = false;

            foreach($sortBy as $k => $column){
                $value = $sortDesc[$k] ?? false;
                $direction = ((trim(strtolower($value)) === 'true' || $value === true || $value == 1) ? 'desc' : 'asc');
                $last = ['ToString', '_to_string', '_to_yes', 'ToYes'];
                foreach($last as $str){
                    $column = Str::beforeLast($column, $str);
                }
                $column = $this->getMapSortColumns($column);
                $hasColumn = Schema::hasColumn($table, $column);
                $hasScope = array_key_exists($column, $this->orderByScopes);
                $scope = ($this->orderByScopes[$column] ?? null);
                // || (
                //     Str::endsWith($column, ($s = 'ToString')) && ($column = Str::beforeLast($column, $s))
                //     && Schema::hasColumn($table, $column)
                // )
                // || (
                //     Str::endsWith($column, ($s = '_to_string')) && ($column = Str::beforeLast($column, $s))
                //     && Schema::hasColumn($table, $column)
                // )
                // || (
                //     Str::endsWith($column, ($s = '_to_yes')) && ($column = Str::beforeLast($column, $s))
                //     && Schema::hasColumn($table, $column)
                // );

                // if (
                //     Schema::hasColumn($query->getModel()->getTable(), $column)
                //     || (Str::endsWith($column, ($s = 'ToString')) && ($column = Str::beforeLast($column, $s))
                //         && Schema::hasColumn($query->getModel()->getTable(), $column))
                // ) {
                //     $query->orderBy($column, $direction);
                //     continue;
                // }

                // if (Str::endsWith($column, ($s = 'ToString')) && ($column = Str::beforeLast($column, $s))
                //     && Schema::hasColumn($query->getModel()->getTable(), $column)
                // ) {
                //     $query->orderBy($column, $direction);
                //     continue;
                // }
                if(($hasColumn || $hasScope) && !$emptyBaseOrder){
                    $emptyBaseOrder = true;
                    $query->getQuery()->orders = [];
                }
                //$hasColumn && !$emptyBaseOrder && ($emptyBaseOrder = true);
                //$emptyBaseOrder && ($query->getQuery()->orders = []);
                //$hasColumn && $query->orderBy($column, $direction);
                //$hasColumn && $query->orderByRaw("CONVERT({$column}, SIGNED) {$direction}");
                //$direction = strtoupper($direction);
                //$hasColumn && $query->orderByRaw("CONVERT(`{$column}`, UNSIGNED) {$direction}");

                if($hasColumn){
                    if(array_key_exists($column, $this->orderByRawColumns)){
                        $query->orderByRaw("CONVERT(`{$column}`, {$this->orderByRawColumns[$column]}) {$direction}");
                    }
                    else{
                        $query->orderBy($column, $direction);
                    }
                }
                elseif($this->hasMapSortColumns($column)){
                    //d(1);
                    $query->orderBy($this->getMapSortColumns($column), $direction);
                }
                if($hasScope){
                    //d($scope,$direction);
                    $query = $model->{$scope}($query, $direction);
                    //d($query->getQuery()->orders);
                }
            }
        }
        //d($query->getQuery());
        return $query;
    }

    /**
     * @param $column
     *
     * @return string
     */
    protected function getMapSortColumns($column): string
    {
        return ($this->mapSortColumns[$column] ?? $column);
    }

    /**
     * @param $column
     *
     * @return bool
     */
    protected function hasMapSortColumns($column): bool
    {
        return array_key_exists($column, $this->mapSortColumns);
    }
}

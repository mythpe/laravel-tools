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
use Illuminate\Database\Eloquent\Relations\Relation;

trait ApplyQueryTrait
{

    /**
     * Keys that will be removed from the filter if empty
     * Example: ['start_date','end_date']
     * @var array
     */
    public array $trimRequestKeys = [];
    /**
     * Auto exclude items by ids
     * Example: 1,2,3
     * @var string
     */
    protected string $autoExcludeKey = 'exclude_self';

    /**
     * Keys that will be removed from the filter if empty
     * Before apply filter
     * Example: ['start_date','end_date']
     * @return void
     */
    public function trimKeysFromRequest(): void
    {
        if (count($this->trimRequestKeys) < 1) {
            return;
        }

        $filters = $this->request->input($this->filterRequestKey, []);
        if ($this->request->has($this->filterRequestKey) && !is_array($filters)) {
            $filters = json_decode($filters, !0);
        }
        if (!is_array($filters)) {
            return;
        }
        $changed = !1;
        foreach ($this->trimRequestKeys as $name) {
            foreach (['from', 'to'] as $str) {
                $key = "{$str}_$name";
                if (array_key_exists($key, $filters) && !$filters[$key]) {
                    unset($filters[$key]);
                    if (!$changed) {
                        $changed = !0;
                    }
                }
            }
            if ($changed) {
                $this->request->merge([$this->filterRequestKey => $filters]);
            }
        }
    }

    /**
     * @param Builder|mixed $builder
     *
     * @return Builder|mixed
     */
    protected function apply($builder = null)
    {
        if ($builder) {
            $this->trimKeysFromRequest();
            $builder = $this->applyExcludeQuery($builder);
            $builder = $this->sortQuery($builder);
            $builder = $this->searchQuery($builder);
            $builder = $this->filerQuery($builder);
        }
        return $builder;
    }

    /**
     * @param Builder|Relation|mixed $query
     *
     * @return mixed
     */
    protected function applyExcludeQuery($query)
    {
        if (($ids = $this->request->input($this->autoExcludeKey))) {
            if (!is_array($ids)) {
                $ids = explode(',', $ids);
            }
            if (is_array($ids) && count($ids) > 0) {
                $query = $query->whereNotIn('id', $ids);
            }
        }
        return $query;
    }
}

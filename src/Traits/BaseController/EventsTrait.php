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
use Myth\LaravelTools\Models\BaseModel as Model;

trait EventsTrait
{
    /**
     * @param $query
     *
     * @return mixed|void
     */
    protected function indexing(&$query)
    {
    }

    /**
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function saving(&$model)
    {
    }

    /**
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function creating(&$model)
    {
    }

    /**
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function saved(&$model)
    {
    }

    /**
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function created(&$model)
    {
    }

    /**
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function updating(&$model)
    {
    }

    /**
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function updated(&$model)
    {
    }

    /**
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function showing(&$model)
    {
    }

    /**
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function deleting(&$model)
    {
    }

    /**
     * @param Model[]|Builder[] $models
     *
     * @return mixed|void
     */
    protected function deletingAll(&$models)
    {
    }

    /**
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function deleted(&$model)
    {
    }

    /**
     * @param Model[]|Builder[] $models
     *
     * @return mixed|void
     */
    protected function deletedAll(&$models)
    {
    }

    /**
     * @param array $rules
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function beforeValidate(&$rules, &$model)
    {
    }

    /**
     * @param array $rules
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function beforeStoreValidate(&$rules, &$model)
    {
    }

    /**
     * @param array $rules
     * @param Model|Builder $model
     *
     * @return mixed|void
     */
    protected function beforeUpdateValidate(&$rules, &$model)
    {
    }
}

<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Myth\LaravelTools\Models\BaseModel as Model;

trait RulesTrait
{
    /**
     * @return array
     */
    public function getRules(): array
    {
        return [];
    }

    /**
     * @param array $rules
     * @param Model|null $model
     *
     * @return array
     */
    protected function storeRules(array $rules = [], &$model = null): array
    {
        return $this->requestRules($rules, $model);
    }

    /**
     * @param array $rules
     * @param Model|null $model
     *
     * @return array
     */
    protected function requestRules(array &$rules = [], &$model = null): array
    {
        return array_merge($rules);
    }

    /**
     * @param array $rules
     * @param Model|null $model
     *
     * @return array
     */
    protected function updateRules(array $rules = [], &$model = null): array
    {
        return $this->requestRules($rules, $model);
    }

    /**
     * @param array|null $rules
     * @param Model|null $model
     */
    protected function makeValidator(array $rules = [], &$model = null): void
    {
        $rules = $rules ?: $this->requestRules($rules, $model);
        $this->request->validate($rules ?: [], $this->getRulesMessages());
    }

    /**
     * @return array
     */
    protected function getRulesMessages(): array
    {
        return [];
    }
}

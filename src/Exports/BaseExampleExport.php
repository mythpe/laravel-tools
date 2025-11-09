<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Myth\LaravelTools\Models\BaseModel;

class BaseExampleExport implements FromArray
{
    public function __construct(
        public BaseModel $model
    )
    {
        //
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $headers = [];
        $fill = method_exists($this->model, 'exampleHeaders') ? $this->model->exampleHeaders() : $this->model->getFillable();
        foreach ($fill as $field) {
            $headers[] = __("attributes.$field");
        }
        return [
            $headers,
        ];
    }
}

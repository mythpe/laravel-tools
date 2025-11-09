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
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Myth\LaravelTools\Models\BaseModel;

class BaseExampleExport implements FromArray, WithEvents
{
    public static ?string $locale = 'en';

    public function __construct(
        public BaseModel $model
    )
    {
        //
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $event->sheet->getDelegate()->setRightToLeft(app()->getLocale() == 'ar');
            },
        ];
    }

    /**
     * @return array
     */
    public function array(): array
    {
        $headers = [];
        $importable = method_exists($this->model, 'getImportable') ? $this->model->getImportable() : $this->model->getFillable();
        $locale = static::$locale;
        foreach ($importable as $key => $value) {
            if (is_numeric($key)) {
                $header = trans_has("attributes.$value") ? __("attributes.$value", [], $locale) : $value;
            }
            else {
                $header = trans_has("attributes.$key") ? __("attributes.$key", [], $locale) : $key;
            }
            $headers[] = $header;
        }

        return [
            $headers,
        ];
    }
}

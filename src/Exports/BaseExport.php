<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class BaseExport extends StringValueBinder implements WithCustomValueBinder, FromCollection,
    WithEvents
{
    /**
     * @var array|\Illuminate\Support\Collection
     */
    public $headers = [];

    /**
     * @var array|\Illuminate\Support\Collection
     */
    public $items = [];

    /**
     * @param  array|\Illuminate\Support\Collection  $headers
     * @param  array|\Illuminate\Support\Collection  $items
     */
    public function __construct($headers = [], $items = [])
    {
        $this->headers = collect($headers);
        $this->items = is_array($items) ? collect($items) : $items;
    }

    /**
     * @return static
     */
    public static function make()
    {
        return new static(...func_get_args());
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $data = [];
        //d($this->headers);
        foreach ($this->headers as $k => $header) {
            $data[] = is_array($header) ? ($header['text'] ?? '') : (trans_has("attributes.$header") ? __("attributes.$header") : $header);
        }
        $data = [$data];

        foreach ($this->items as $itemKey => $item) {
            $v = [];
            foreach ($this->headers as $headerKey => $header) {
                //d($item);
                $v[] = is_string($item) ? $item : (is_array($header) ? ($item[($header['value'] ?? '')] ?? '') : ($item[$header] ?? ''));
            }
            $data[] = $v;
        }
        //d($data);
        return collect($data);
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
}

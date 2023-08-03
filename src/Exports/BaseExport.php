<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Exports;

use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class BaseExport extends StringValueBinder implements WithCustomValueBinder, FromCollection,
    WithEvents
{
    /**
     * @var array|Collection
     */
    public $headers = [];

    /**
     * @var array|Collection
     */
    public $items = [];

    /**
     * @param array|Collection $headers
     * @param array|Collection $items
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
     * @return Collection
     */
    public function collection()
    {
        $data = [];
        //d($this->headers);
        foreach ($this->headers as $header) {
            if (is_array($header)) {
                $value = ($header['text'] ?? ($header['label'] ?? ($header['field'] ?? ($header['name'] ?? ''))));
            } else {
                $value = trans_has("attributes.$header") ? __("attributes.$header") : $header;
            }
            if ($value == 'control') {
                continue;
            }
            $data[] = $value;
        }
        //d($data);
        $data = [$data];

        foreach ($this->items as $item) {
            $v = [];
            foreach ($this->headers as $header) {
                //d($item);
                $r = is_string($item) ? $item : (is_array($header) ? ($item[($header['value'] ?? '')] ?? ($item[($header['field'] ?? '')] ?? ($item[($header['name'] ?? '')] ?? ''))) : ($item[$header] ?? ''));
                $v[] = $r instanceof MissingValue ? null : $r;
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


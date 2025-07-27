<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Exports;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Resources\MissingValue;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

// class BaseExport extends StringValueBinder implements WithCustomValueBinder, FromCollection, WithEvents
class BaseExport extends StringValueBinder implements ShouldAutoSize, WithCustomValueBinder, FromView, WithEvents
{
    use Exportable, RegistersEventListeners;

    /**
     * @param array|Collection $headers
     * @param array|Collection $rows
     * @param array|Collection $footer
     * @param string $view
     */
    public function __construct(
        public array | Collection $headers = [],
        public array | Collection $rows = [],
        public array | Collection $footer = [],
        public string             $view = '4myth-tools::excel',
    )
    {
    }

    /**
     * @return static
     */
    public static function make(): static
    {
        return new static(...func_get_args());
    }

    /**
     * @param AfterSheet $event
     * @return void
     */
    public static function afterSheet(AfterSheet $event): void
    {
        $event->sheet->getDelegate()->setRightToLeft(app()->getLocale() == 'ar');
    }

    /**
     * @return Collection
     */
    public function collection(): Collection
    {
        $data = [];
        foreach ($this->headers as $header) {
            if (is_array($header)) {
                $value = ($header['text'] ?? ($header['label'] ?? ($header['field'] ?? ($header['name'] ?? ''))));
            }
            else {
                $value = trans_has("attributes.$header") ? __("attributes.$header") : $header;
            }
            if ($value == 'control') {
                continue;
            }
            $data[] = $value;
        }
        $data = [$data];

        foreach ($this->rows as $item) {
            $v = [];
            foreach ($this->headers as $header) {
                $r = is_string($item) ? $item : (is_array($header) ? ($item[($header['value'] ?? '')] ?? ($item[($header['field'] ?? '')] ?? ($item[($header['name'] ?? '')] ?? ''))) : ($item[$header] ?? ''));
                $r = $r instanceof MissingValue ? '' : $r;
                $v[] = $r == 0 ? '0' : $r;
            }
            $data[] = $v;
        }
        if (!empty($this->append)) {
            $data = [...$data, ...$this->append];
        }
        return collect($data);
    }

    /**
     * Generates a view representation with the provided data.
     *
     * @return View The rendered view instance based on the specified template and data.
     */
    public function view(): View
    {
        $headers = $this->getHeaders();
        $rows = $this->getRows();
        return view($this->view, [
            'headers' => $headers,
            'rows'    => $rows,
            'footer'  => $this->footer,
        ]);
    }

    protected function getHeaders(): array
    {
        $result = [];
        foreach ($this->headers as $header) {
            if (is_array($header)) {
                $value = ($header['text'] ?? ($header['label'] ?? ($header['field'] ?? ($header['name'] ?? ''))));
            }
            else {
                $value = trans_has("attributes.$header") ? __("attributes.$header") : $header;
            }
            if ($value == 'control') {
                continue;
            }
            $result[] = $value;
        }
        return $result;
    }

    protected function getRows(): array
    {
        $result = [];
        foreach ($this->rows as $item) {
            $v = [];
            foreach ($this->headers as $header) {
                $r = is_string($item) ? $item : (is_array($header) ? ($item[($header['value'] ?? '')] ?? ($item[($header['field'] ?? '')] ?? ($item[($header['name'] ?? '')] ?? ''))) : ($item[$header] ?? ''));
                $r = $r instanceof MissingValue ? '' : $r;
                $v[] = $r == 0 ? '0' : $r;
            }
            $result[] = $v;
        }
        return $result;
    }
}

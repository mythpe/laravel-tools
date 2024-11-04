<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Myth\LaravelTools\Exports\BaseExport;
use Myth\LaravelTools\Http\Resources\ApiCollectionResponse;
use Myth\LaravelTools\Http\Resources\ApiResource;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait PaginateTrait
{
    /**
     * @var string request key of rows will append on export.
     */
    const EXPORT_APPEND_KEY = 'myth_append_export';
    /**
     * Request key of index type.
     * Values: pdf, excel, index
     * @var string
     */
    const INDEX_TYPE_KEY = 'indexType';
    /** @var int */
    public int $page = 1;
    /**
     * Request page key
     *
     * @var string
     */
    public string $pageKey = 'page';
    /** @var int|null */
    public ?int $limit = null;
    /**
     * Request limit key
     *
     * @var string
     */
    public string $limitKey = 'limit';
    /** @var int */
    public int $itemsPerPage = 15;
    /**
     * request key name
     *
     * @var string
     */
    public string $itemsPerPageKey = 'itemsPerPage';
    /**
     * request key name
     *
     * @var string
     */
    public string $controlHeaderKey = 'control';

    /**
     * Get class of export data
     *
     * @return string
     * @uses Maatwebsite
     */
    public static function getControllerExcelExportClass(): string
    {
        return config('4myth-tools.ExcelExportClass', BaseExport::class);
    }

    /**
     * @return string
     */
    public static function getControllerPdfView(): string
    {
        return config('4myth-tools.snappy_pdf_view', '4myth-tools::layouts.table_pdf');
    }

    /**
     * @return string
     */
    public function controllerIndexType(): string
    {
        return $this->request->input(self::INDEX_TYPE_KEY, $default = 'index') ?: $default;
    }

    /**
     * @return bool
     */
    public function isExcelIndex(): bool
    {
        return $this->controllerIndexType() == 'excel';
    }

    /**
     * @return bool
     */
    public function isPdfIndex(): bool
    {
        return $this->controllerIndexType() == 'pdf';
    }

    /**
     * @return bool
     */
    public function isIndexType(): bool
    {
        return $this->controllerIndexType() === 'index';
    }

    /**
     * @return bool
     */
    public function isExportIndex(): bool
    {
        return $this->isPdfIndex() || $this->isExcelIndex();
    }

    /**
     * @return array|callable
     */
    public function exportAppendRows(): array | callable
    {
        return $this->request->input(static::EXPORT_APPEND_KEY, $default = []) ?: $default;
    }

    /**
     * @param mixed|Builder|Model $query
     * @param mixed|string|ApiResource|null $transformer
     * @param mixed|string|null $excelClass
     *
     * @return JsonResponse|Response|ApiCollectionResponse|BinaryFileResponse
     */
    protected function indexResponse($query = null, ?string $transformer = null, $excelClass = null)
    {
        $request = $this->request;
        $query = is_null($query) ? static::$controllerModel::whereNull('id') : $query;
        $transformer = is_null($transformer) ? $this->getIndexTransformer() : $transformer;
        $indexType = $this->controllerIndexType();
        $modelName = Str::pluralStudly(class_basename($query->getModel()));
        $pageTitle = $request->input(($a = 'pageTitle')) ? $request->input($a) : (trans_has(($a = "choice.{$modelName}")) ? trans_choice($a, 2) : $modelName);
        if ($indexType == 'pdf' || $indexType == 'excel') {
            $items = $request->input(ApiResource::$itemsRequestKey, []);
            $headers = $request->input(ApiResource::$headerItemsRequestKey, []);

            if (!$items) {
                $ids = $request->input('ids', []);
                $query = $this->apply($query);
                if (!empty($ids) && is_array($ids)) {
                    $query->whereIn($query->getModel()->getKeyName(), $ids);
                }
                $items = $transformer::collection($query->get())->toArray($this->request);
            }
            else {
                $items = $transformer::collection($query->whereIn('id', $items)->get())->toArray($this->request);
            }

            if (!is_array($headers)) {
                $headers = [];
            }
            if (!is_array($items)) {
                $items = [];
            }
            //d($headers);
            $fileName = "Export-".(auth()->id() ?: 0);
            $appendRows = $this->exportAppendRows();
            $appendRows = is_callable($appendRows) ? $appendRows($items, $headers) : $appendRows;
            $headers = collect($headers)->filter(fn($v) => is_array($v) ? !in_array($this->controlHeaderKey, [
                ($v['field'] ?? null),
                ($v['name'] ?? null),
            ]) : $v != $this->controlHeaderKey)->values()->toArray();
            if ($indexType == 'excel') {
                $fileName = "{$fileName}.xlsx";
                /** @var BaseExport $excelClass */
                $excelClass = is_null($excelClass) ? static::getControllerExcelExportClass() : $excelClass;
                if ($request->input('toUrl')) {
                    $disk = Storage::disk('excel');
                    Excel::store($excelClass::make($headers, $items, $appendRows), $fileName, 'excel');
                    return $this->successResponse(['data' => ['url' => $disk->url($fileName)]]);
                }
                /** @var BinaryFileResponse $e */
                return Excel::download($excelClass::make($headers, $items, $appendRows), $fileName, null, [
                    'File-Name'                     => $fileName,
                    'Access-Control-Expose-Headers' => ['Content-Disposition', 'File-Name'],
                ])->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName, $fileName);
            }
            $compact = [
                'headerItems'   => $headers,
                'items'         => $items,
                'pageTitle'     => $pageTitle,
                'appendRows'    => $appendRows,
                'usePublicPath' => !0,
            ];

            $disk = Storage::disk('pdf');
            $fileName = "{$fileName}.pdf";
            $path = $disk->path($fileName);

            $pdf = SnappyPdf::loadView(static::getControllerPdfView(), $compact);
            $pdf->setOption('title', $pageTitle);

            if ($request->input('toUrl')) {
                $pdf->save($path, !0);
                return $this->successResponse([
                    'data' => ['url' => $disk->url($fileName),],
                ]);
            }

            /** Inline */
            // return $pdf->inline($fileName);

            /** Download */
            // return $pdf->download($fileName);
            return response($pdf->output(), 200, [
                'Content-Type'                  => 'application/pdf',
                'Content-Disposition'           => 'attachment; filename="'.$fileName.'"',
                'File-Name'                     => $fileName,
                'Access-Control-Expose-Headers' => ['Content-Disposition', 'File-Name'],
            ]);
        }
        $responseClass = config('4myth-tools.api_collection_response_class', ApiCollectionResponse::class);
        return new $responseClass($this->paginate($query), $transformer);
    }

    /**
     * @return string
     */
    protected function getIndexTransformer(): string
    {
        return static::$indexTransformer;
    }

    /**
     * Do calc for Pagination.
     *
     * @param Builder $query
     *
     * @return Builder|mixed
     */
    protected function paginate($query)
    {
        // d($query);
        $query = $this->apply($query);
        if ($this->itemsPerPage == -1 || !is_null($this->limit)) {
            if (!is_null($this->limit)) {
                $query->limit((int) ($this->limit));
            }

            $limit = !is_null($this->limit) ? $this->limit : $query->toBase()->limit;
            $this->itemsPerPage == -1 && ($this->page = 1);
            $this->itemsPerPage = !is_null($limit) ? $limit : $query->count();
        }
        return $query->paginate((int) $this->itemsPerPage, ['*'], 'page', (int) $this->page);
    }

    /**
     * @return string|mixed
     */
    protected function getControllerTransformer()
    {
        return static::$controllerTransformer;
    }

    /**
     * @param Request $request
     *
     * @return $this
     */
    protected function iniPaginateRequest(Request $request): self
    {
        $this->itemsPerPage = (int) $request->input($this->itemsPerPageKey, $this->itemsPerPage);
        $this->page = (int) $request->input($this->pageKey, $this->page);
        $this->limit = $request->has($this->limitKey) ? (int) $request->input($this->limitKey) : null;
        return $this;
    }
}

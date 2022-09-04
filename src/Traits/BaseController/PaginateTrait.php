<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Barryvdh\Snappy\Facades\SnappyPdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Myth\LaravelTools\Exports\BaseExport;
use Myth\LaravelTools\Http\Resources\ApiCollectionResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait PaginateTrait
{
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
     * @param  mixed|Builder|\Illuminate\Database\Eloquent\Model  $query
     * @param  mixed|string|\Myth\LaravelTools\Http\Resources\ApiResource|null  $transformer
     * @param  mixed|string|null  $excelClass
     *
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Response|\Myth\LaravelTools\Http\Resources\ApiCollectionResponse|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function indexResponse($query = null, ?string $transformer = null, $excelClass = null)
    {
        $request = $this->request;
        $query = is_null($query) ? static::$controllerModel::whereNull('id') : $query;
        $transformer = is_null($transformer) ? $this->getIndexTransformer() : $transformer;
        $indexType = $request->input('indexType');
        $modelName = Str::pluralStudly(class_basename($query->getModel()));
        $pageTitle = $request->input(($a = 'pageTitle')) ? $request->input($a) : (trans_has(($a = "choice.{$modelName}")) ? trans_choice($a, 2) : $modelName);
        if ($indexType == 'pdf' || $indexType == 'excel') {
            $items = $request->input('items', []);
            $headers = $request->input('headerItems', []);

            if (!$items) {
                $ids = $request->input('ids', []);
                $query = $this->apply($query);
                if (!empty($ids)) {
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

            if ($indexType == 'excel') {
                $fileName = "{$fileName}.xlsx";
                /** @var BaseExport $excelClass */
                $excelClass = is_null($excelClass) ? static::getControllerExcelExportClass() : $excelClass;
                if ($this->request->input('toUrl')) {
                    $disk = Storage::disk('excel');
                    Excel::store($excelClass::make($headers, $items), $fileName, 'excel');
                    return $this->successResponse([
                        'data' => ['url' => $disk->url($fileName),],
                    ]);
                }
                //d($fileName);
                /** @var \Symfony\Component\HttpFoundation\BinaryFileResponse $e */
                return Excel::download($excelClass::make($headers, $items), "{$fileName}")->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $fileName);
                // response()->headers->set('content-disposition',"filename={$fileName}");
                // $r->headers->set('content-disposition', "filename={$fileName}");
                // d(2);
                //return $r;
                // dd($r);
                //return Excel::download($excelClass::make($headers, $items), "{$fileName}");
                // $disk = Storage::disk('excel');
                // Excel::store($excelClass::make($headers, $items), $fileName, 'excel');
                // return response()->redirectTo($disk->url($fileName));

                // response()->headers->set('content-disposition',"filename={$fileName}");
                // return  $disk->get($fileName);
            }
            //d($headers, $items);
            $headers = collect($headers)->filter(fn($v) => is_array($v) ? (($v['value'] ?? null) != 'control' && ($v['field'] ?? null) != 'control') : $v != 'control')->values()->toArray();
            $compact = [
                'headerItems' => $headers,
                'items'       => $items,
                'pageTitle'   => $pageTitle,
            ];

            $disk = Storage::disk('pdf');
            $fileName = "{$fileName}.pdf";
            $path = $disk->path($fileName);

            $pdf = SnappyPdf::loadView(static::getControllerPdfView(), $compact);
            $pdf->setOption('title', $pageTitle);

            if ($this->request->input('toUrl')) {
                $pdf->save($path, !0);
                return $this->successResponse([
                    'data' => ['url' => $disk->url($fileName),],
                ]);
            }

            // return $pdf->output();
            /** Inline */
            return $pdf->inline($fileName);

            /** Download */ //$pdf->save($path, !0);
            //
            //$size = $disk->getSize($fileName);
            //$disk->delete($fileName);
            //return $pdf->download($fileName)->header('Content-Length', $size);

            // response()->headers->set('content-disposition',"filename={$fileName}");
            // return response()->redirectTo($disk->url($fileName));
            // return $disk->download($fileName, $fileName, [
            //     'Location'                    => config('app.url'),
            //     'Access-Control-Allow-Origin' => '*',
            // ]);
            // return $disk->url($fileName);
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
        return config('4myth-tools.snappy_pdf_view', '4myth-tools::layouts.pdf_table');
    }

    /**
     * Do calc for Pagination.
     *
     * @param  Builder  $query
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
     * @param  \Illuminate\Http\Request  $request
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

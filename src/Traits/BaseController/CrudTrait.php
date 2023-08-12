<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Myth\LaravelTools\Http\Resources\ApiCollectionResponse;
use Myth\LaravelTools\Http\Resources\ApiResource;
use Myth\LaravelTools\Models\BaseModel as Model;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait CrudTrait
{
    /**
     * @var string|Model
     */
    public static string $controllerModel = Model::class;

    /**
     * Name of model in URI
     *
     * @var string|Model
     */
    public static string $routeParameterModel = Model::class;

    /**
     * @var string
     */
    public static string $controllerTransformer = ApiResource::class;

    /**
     * @var string
     */
    public static string $indexTransformer = ApiResource::class;

    /**
     * With query index
     *
     * @var array
     */
    public array $with = [];

    /**
     * with Count query index
     *
     * @var array
     */
    public array $withCount = [];

    /**
     * Range parameters to validation if equal 0 remove it
     *
     * @var array
     */
    public array $validationRangeParameters = [];

    /**
     * The key of request to load model relations
     *
     * @var string
     */
    public string $requestWithKey = 'requestWith';

    /**
     * @var Model
     */
    protected $updatedModel;

    /**
     * @var Model
     */
    protected $storedModel;

    /**
     * Sort query as latest
     *
     * @var bool|string|null
     */
    protected $latest = null;

    /**
     * Sort query as oldest
     *
     * @var bool|string|null
     */
    protected $oldest = null;

    /**
     * This used to show only active of models
     *
     * @var bool
     */
    protected bool $isIndexActiveOnly = !1;

    /**
     * Map keys from request to fill model
     * ruleKey => fillableKey
     * Example: ['customer_id' => 'user_id']
     *
     * @var array
     */
    protected array $mapFromRequest = [];

    /**
     * Check from model relations before destroying
     *
     * @var array
     */
    protected array $checkBeforeDestroy = [];
    /**
     * Auto save model image after saved event
     * @var bool
     */
    protected bool $autoSavingImage = !1;

    /**
     * @return array
     */
    public static function getInsertModelImageOptions(): array
    {
        $model = self::$controllerModel;
        return [$model::$mediaSingleCollection, $model::$mediaSingleCollection];
    }

    /**
     * @return Response|mixed|BinaryFileResponse|void|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function indexActiveOnly()
    {
        $this->isIndexActiveOnly = !0;
        return $this->allIndex(...func_get_args());
    }

    /**
     * @return JsonResponse|Response|mixed|ApiCollectionResponse|BinaryFileResponse|void|null
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function allIndex()
    {
        $this->itemsPerPage = $this->request->input($this->itemsPerPageKey, -1);
        $this->page = $this->request->input($this->pageKey, 1);
        return $this->index(...func_get_args());
    }

    /**
     * @return JsonResponse|Response|mixed|ApiCollectionResponse|BinaryFileResponse
     */
    public function index()
    {
        /** @var Builder $query */
        $query = static::$controllerModel;
        $args = func_get_args();
        $query = ($args[0] ?? $query::query());
        $transformer = ($args[1] ?? $this->getIndexTransformer());
        $excelClass = ($args[2] ?? null);

        $this->isIndexActiveOnly && $query->activeOnly();
        if ($this->latest) {
            $column = $this->latest;
            if (is_string($column) && array_key_exists($column, $this->orderByRawColumns)) {
                $query->orderByRaw("CONVERT(`{$column}`, {$this->orderByRawColumns[$column]}) desc");
            }
            else {
                if (is_array($this->latest)) {
                    foreach ($this->latest as $item) {
                        $query->latest($item);
                    }
                }
                else {
                    $query->latest($this->latest === !0 ? null : $this->latest);
                }
            }
        }

        if ($this->oldest) {
            $column = $this->oldest;
            if (is_string($column) && array_key_exists($column, $this->orderByRawColumns)) {
                $query->orderByRaw("CONVERT(`{$column}`, {$this->orderByRawColumns[$column]}) asc");
            }
            else {
                if (is_array($this->oldest)) {
                    foreach ($this->oldest as $item) {
                        $query->oldest($item);
                    }
                }
                else {
                    $query->oldest($this->oldest === !0 ? null : $this->oldest);
                }
            }
        }

        if (($r = $this->indexing($query))) {
            return $r;
        }
        $with = $this->with;
        /** @var Model $model */
        $model = $query->getModel();
        if (($uid = $this->request->input('uid')) && $query & in_array('user_id', $model->getFillable())) {
            $query->where('user_id', $uid);
        }
        //d($this->request->all());

        /**
         * | This for General relations to append of query
         */
        if (($requestWith = $this->request->input($this->requestWithKey))) {
            !is_array($requestWith) && ($requestWith = explode(',', $requestWith));
            foreach ($requestWith as $value) {
                if (method_exists($model, $value) && !in_array($value, $with)) {
                    $with[] = $value;
                }
            }
        }
        $with = array_filter(array_unique($with));
        $withCount = array_filter(array_unique($this->withCount));
        $query->with($with)->withCount($withCount);
        // dd($with);
        return $this->indexResponse($query, $transformer, $excelClass);

    }

    /**
     * @return JsonResponse
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function store(): JsonResponse
    {
        $this->storedModel = new static::$controllerModel;
        /** @var Model|mixed $model */
        $model =& $this->storedModel;

        $rules = $this->storeRules([], $model);
        $rules = $this->requestRules($rules, $model);
        /** Events */
        if (($r = $this->beforeStoreValidate($rules, $model))) {
            return $r;
        }
        if (($r = $this->beforeValidate($rules, $model))) {
            return $r;
        }
        $this->makeValidator($rules, $model);
        // $keys = array_keys($rules);
        $fill = array_merge($this->dataGet(array_keys($rules)), $this->getMapFromRequest());
        // d($fill);
        $model->fill($fill);
        /** Events */
        if (($r = $this->creating($model))) {
            return $r;
        }
        if (($r = $this->saving($model))) {
            return $r;
        }
        $model->save();
        /** Events */
        if (($r = $this->created($model))) {
            return $r;
        }
        if (($r = $this->saved($model))) {
            return $r;
        }
        if ($this->autoSavingImage) {
            if (($r = $this->insertModelImage($model))) {
                return $r;
            }
        }
        $_m = '_message';
        $this->request->merge([$_m => $this->request->input($_m, __("messages.store_success"))]);
        return $this->show($model);
    }

    /**
     * Insert auto image of model
     * @param Model $model
     *
     * @return mixed|void
     */
    public function insertModelImage(&$model)
    {
        [$fileKey, $collection] = self::getInsertModelImageOptions();
        $request = $this->request;
        if ($request->input("${fileKey}_removed")) {
            $model->clearMediaCollection($collection ?: 'default');
        }
        if ($request->hasFile($fileKey) || $request->input($fileKey)) {
            try {
                $model->addModelMedia($fileKey);
            }
            catch (Exception $exception) {
                return $this->errorResponse($exception->getMessage());
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param Model|Builder $model
     *
     * @return JsonResponse|mixed|void
     */
    public function show($model)
    {
        if ($r = $this->showing($model)) {
            return $r;
        }
        $requestWith = $this->request->input($this->requestWithKey, []);
        if (!is_array($requestWith)) {
            $requestWith = $requestWith ? explode(',', $requestWith) : [];
        }
        $with = array_unique(array_merge(static::RELATIONS, $requestWith));
        return $this->resource($this->getControllerTransformer()::make($model->load($with)), $this->request->input('_message'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Model|Builder $model
     *
     * @return JsonResponse|mixed|void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function update($model)
    {
        $this->updatedModel =& $model;
        $rules = $this->updateRules([], $model);
        $rules = $this->requestRules($rules, $model);
        /** Events */
        if (($r = $this->beforeUpdateValidate($rules, $model))) {
            return $r;
        }
        if (($r = $this->beforeValidate($rules, $model))) {
            return $r;
        }
        $this->makeValidator($rules, $model);
        // $keys = array_keys($rules);
        // $fill = array_merge($this->request->only($keys), $this->getMapFromRequest());
        $fill = array_merge($this->dataGet(array_keys($rules), !1), $this->getMapFromRequest());
        // d($fill);
        $model->fill($fill);
        /** Events */
        if (($r = $this->updating($model))) {
            return $r;
        }
        if (($r = $this->saving($model))) {
            return $r;
        }
        $model->save();
        /** Events */
        if (($r = $this->updated($model))) {
            return $r;
        }
        if (($r = $this->saved($model))) {
            return $r;
        }
        if ($this->autoSavingImage) {
            if (($r = $this->insertModelImage($model))) {
                return $r;
            }
        }
        $_m = '_message';
        $this->request->merge([$_m => $this->request->input($_m, __("messages.updated_success"))]);
        return $this->show($model);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Model|Builder $model
     *
     * @return JsonResponse|mixed|void
     */
    public function destroy($model)
    {
        if (($r = $this->deleting($model))) {
            return $r;
        }
        /** @var \Illuminate\Database\Eloquent\Model|Model $user */
        if (($user = auth()->user()) && $model->is($user)) {
            return $this->errorResponse(__("messages.deleted_failed"));
        }

        foreach ($this->checkBeforeDestroy as $relation) {
            if ($model->$relation()->exists()) {
                return $this->errorResponse(__("messages.can_not_deleted"));
            }
        }

        $model->delete();

        if (($r = $this->deleted($model))) {
            return $r;
        }
        $_m = '_message';
        return $this->resource($this->request->input($_m, __('messages.deleted_success')));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return JsonResponse|mixed|void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function destroyAll()
    {
        $model = $this->request->input('ids', []);
        if (!is_array($model)) {
            $model = [];
        }
        if (count($model) < 1) {
            return $this->errorResponse(__("messages.no_delete_selected"));
        }
        if (($r = $this->deletingAll($model))) {
            return $r;
        }

        /** @var Builder $builder */
        $builder = static::$controllerModel::query();

        if (count($model) > 0) {
            $builder->whereIn('id', $model);
        }

        try {
            /** @var Collection $models */
            $models = $builder->get();
            foreach ($models as $m) {
                foreach ($this->checkBeforeDestroy as $relation) {
                    if ($m->$relation()->exists()) {
                        return $this->errorResponse(__("messages.can_not_deleted"));
                    }
                }
                $m->delete();
            }
            // $models->each(function($m) use ($model){});
        }
        catch (Exception$exception) {
            return $this->errorResponse($exception->getMessage());
        }
        if (($r = $this->deletedAll($model))) {
            return $r;
        }
        $_m = '_message';
        return $this->resource($this->request->input($_m, __("messages.deleted_success")));
    }

    /**
     * @return Model|mixed
     */
    public function getStoredModel()
    {
        return $this->storedModel;
    }

    /**
     * @return Model|mixed
     */
    public function getUpdatedModel()
    {
        return $this->updatedModel;
    }

    /**
     * Check from request parameters range (from-to) if equal 0 remove it.
     *
     * @return void
     */
    public function validateRangeRangeParameters(): void
    {
        if (count($this->validationRangeParameters) < 1) {
            return;
        }

        $filter = $this->request->input('filter', []);
        if ($this->request->has('filter') && !is_array($filter)) {
            $filter = json_decode($filter, !0);
        }
        if (!is_array($filter)) {
            return;
        }
        foreach ($this->validationRangeParameters as $name) {
            $from = "from_{$name}";
            $to = "to_{$name}";
            if (array_key_exists($from, $filter) && !$filter[$from]) {
                unset($filter[$from]);
            }
            if (array_key_exists($to, $filter) && !$filter[$to]) {
                unset($filter[$to]);
            }
            $this->request->merge(['filter' => $filter]);
        }
    }

    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function getMapFromRequest(): array
    {
        $array = [];
        foreach ($this->mapFromRequest as $rule => $request) {
            $array[$request] = $this->request->input($rule);
        }
        return $array;
    }

    /**
     * @return Model|mixed
     */
    protected function getBindModel()
    {
        if (app()->runningInConsole()) {
            return new static::$controllerModel;
        }
        $name = class_basename(static::$controllerModel);
        if (!($model = $this->request->{$name})) {
            $name = static::$routeParameterModel;
            return $this->request->route()?->parameter($name) ?: new static::$controllerModel;
        }
        return $model;
    }

    /**
     * @param Builder|mixed $builder
     *
     * @return Builder|mixed
     */
    protected function apply($builder = null)
    {
        if ($builder) {
            $this->validateRangeRangeParameters();
            $builder = $this->sortQuery($builder);
            $builder = $this->searchQuery($builder);
            $builder = $this->filerQuery($builder);
        }
        return $builder;
    }
}

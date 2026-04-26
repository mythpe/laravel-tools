<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Traits\BaseController;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Myth\LaravelTools\Exports\BaseExampleExport;
use Myth\LaravelTools\Http\Resources\ApiResource;
use Myth\LaravelTools\Models\BaseModel as Model;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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
     * Set locale of example export.
     * Default is 'en'
     * @var string|null
     */
    public static ?string $exampleLocale = 'en';
    /**
     * Model events
     * 1. index
     * 2. saving
     * 3. creating
     * 4. saved
     * 5. created
     * 6. updating
     * 7. updated
     * 8. show
     * 9. deleting
     * 9. deleted
     * 10. deletingAll
     * 11. deletedAll
     * @var array
     */
    protected static array $modelEvents = [];
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
     * The key of request to load model relations
     *
     * @var string
     */
    public string $requestWithKey = 'requestWith';

    /**
     * @var Model
     */
    public $updatedModel;

    /**
     * @var Model
     */
    public $storedModel;

    /**
     * Sort query as latest
     *
     * @var array|bool|string|null
     */
    public string | array | bool | null $latest = null;

    /**
     * Sort query as oldest
     *
     * @var array|bool|string|null
     */
    public string | array | bool | null $oldest = null;

    /**
     * This used to show only active of models
     *
     * @var bool
     */
    public bool $isIndexActiveOnly = !1;

    /**
     * Map keys from request to fill model
     * ruleKey => fillableKey
     * Example: ['customer_id' => 'user_id']
     *
     * @var array
     */
    public array $mapFromRequest = [];

    /**
     * Check from model relations before destroying
     *
     * @var array
     */
    public array $checkBeforeDestroy = [];

    /**
     * Auto save model image after saved event
     * @var bool
     */
    public bool $autoSavingImage = !1;

    /**
     * @param string $event
     * @param callable|string $callback
     * @return void
     */
    public static function registerModelEvent(string $event, callable | string $callback): void
    {
        $className = static::class;
        static::$modelEvents[$event][$className] ??= [];
        static::$modelEvents[$event][$className][] = $callback;
    }

    /**
     * @param string|null $event
     * @param string|null $className
     * @return array
     */
    public static function getModelEvent(?string $event = null, ?string $className = null): array
    {
        if ($event === null) {
            return static::$modelEvents;
        }
        if ($className !== null) {
            return static::$modelEvents[$event][$className] ?? [];
        }
        return static::$modelEvents[$event] ?? [];
    }

    /**
     * @return array
     */
    public static function getInsertModelImageOptions(): array
    {
        $model = self::$controllerModel;
        return [$model::$mediaSingleCollection, $model::$mediaSingleCollection];
    }

    /**
     * @return JsonResponse|mixed|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function indexActiveOnly()
    {
        $this->isIndexActiveOnly = !$this->request->input('qid', !1);
        return $this->allIndex(...func_get_args());
    }

    /**
     * @return JsonResponse|mixed|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function allIndex()
    {
        $this->itemsPerPage = $this->request->input($this->itemsPerPageKey, 150);
        $this->page = $this->request->input($this->pageKey, 1);
        return $this->index(...func_get_args());
    }

    /**
     * @return JsonResponse|mixed|void
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function index()
    {
        $query = static::$controllerModel;
        $args = func_get_args();
        /** @var Builder $query */
        $query = ($args[0] ?? $query::query());
        $transformer = ($args[1] ?? $this->getIndexTransformer());
        $excelClass = ($args[2] ?? null);

        if ($this->isIndexActiveOnly) {
            if (method_exists($query, 'scopeActiveOnly') || method_exists($query->getModel(), 'scopeActiveOnly')) {
                $query->activeOnly();
            }
        }
        if (($r = $this->indexing($query))) {
            return $r;
        }

        if (!!$this->oldest) {
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

        if (!!$this->latest) {
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

        $with = $this->with;
        /** @var Model $model */
        $model = $query->getModel();
        if (($uid = $this->request->input('uid')) && in_array('user_id', $model->getFillable())) {
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

        $events = static::getModelEvent('index', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($query);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $query);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result) {
                $query = $result;
            }
        }

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
        $model->fill($fill);

        /** Events */
        if (($r = $this->creating($model))) {
            return $r;
        }
        $events = static::getModelEvent('creating', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $model = $result;
            }
        }

        if (($r = $this->saving($model))) {
            return $r;
        }
        $events = static::getModelEvent('saving', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $model = $result;
            }
        }

        $model->save();
        /** Events */
        if (($r = $this->created($model))) {
            return $r;
        }
        $events = static::getModelEvent('created', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $model = $result;
            }
        }

        if (($r = $this->saved($model))) {
            return $r;
        }
        $events = static::getModelEvent('saved', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $model = $result;
            }
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
     * Insert auto image of a model
     * @param Model $model
     *
     * @return mixed|void
     */
    public function insertModelImage(&$model)
    {
        [$fileKey, $collection] = static::getInsertModelImageOptions();
        $request = $this->request;
        if ($request->input("{$fileKey}_removed")) {
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
        $events = static::getModelEvent('show', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $model = $result;
            }
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
        $model->fill($fill);

        /** Events */
        if (($r = $this->updating($model))) {
            return $r;
        }
        $events = static::getModelEvent('updating', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $model = $result;
            }
        }

        if (($r = $this->saving($model))) {
            return $r;
        }
        $events = static::getModelEvent('saving', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $model = $result;
            }
        }

        $model->save();
        /** Events */
        if (($r = $this->updated($model))) {
            return $r;
        }
        $events = static::getModelEvent('updated', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $model = $result;
            }
        }

        if (($r = $this->saved($model))) {
            return $r;
        }
        $events = static::getModelEvent('saved', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
            if ($result instanceof \Illuminate\Database\Eloquent\Model) {
                $model = $result;
            }
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
        /** @var \Illuminate\Database\Eloquent\Model|Model $user */
        if (($user = auth()->user()) && $model->is($user)) {
            return $this->errorResponse(__("messages.deleted_failed"));
        }
        if (($r = $this->deleting($model))) {
            return $r;
        }
        $events = static::getModelEvent('deleting', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
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
        $events = static::getModelEvent('deleted', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($model);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $model);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
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
        $deleteIds = $this->request->input('ids', []);
        if (!is_array($deleteIds)) {
            $deleteIds = [];
        }
        if (count($deleteIds) < 1) {
            return $this->errorResponse(__("messages.no_delete_selected"));
        }
        if (($r = $this->deletingAll($deleteIds))) {
            return $r;
        }
        $events = static::getModelEvent('deletingAll', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($deleteIds);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $deleteIds);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
        }

        $builder = static::$controllerModel::query();

        if (count($deleteIds) > 0) {
            $builder->whereIn('id', $deleteIds);
        }

        try {
            /** @var Collection $models */
            $models = $builder->get();
            foreach ($models as $model) {
                foreach ($this->checkBeforeDestroy as $relation) {
                    if ($model->$relation()->exists()) {
                        return $this->errorResponse(__("messages.can_not_deleted"));
                    }
                }
                $model->delete();
                $events = static::getModelEvent('deleted', static::class);
                foreach ($events as $callback) {
                    $result = null;
                    if (is_string($callback)) {
                        $result = $this->{$callback}($model);
                    }
                    elseif (is_callable($callback)) {
                        $result = $callback($this, $model);
                    }
                    if ($result instanceof JsonResponse) {
                        return $result;
                    }
                }
            }
        }
        catch (Exception$exception) {
            return $this->errorResponse($exception->getMessage());
        }

        if (($r = $this->deletedAll($deleteIds))) {
            return $r;
        }
        $events = static::getModelEvent('deletedAll', static::class);
        foreach ($events as $callback) {
            $result = null;
            if (is_string($callback)) {
                $result = $this->{$callback}($deleteIds);
            }
            elseif (is_callable($callback)) {
                $result = $callback($this, $deleteIds);
            }
            if ($result instanceof JsonResponse) {
                return $result;
            }
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
     * @param object|Model $model
     * @return JsonResponse
     */
    public function clone(object $model): JsonResponse
    {
        $clone = $model->cloneModel();
        return $this->resource(__('messages.clone_success'));
    }

    /**
     * @return array
     */
    public function getMapFromRequest(): array
    {
        $array = [];
        foreach ($this->mapFromRequest as $rule => $request) {
            if ($this->request->input($rule)) {
                $array[$request] = $this->request->input($rule);
            }
        }
        return $array;
    }

    /**
     * @return Model
     */
    public function getBindModel(): Model
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
     * @return JsonResponse
     */
    public function import()
    {
        $request = $this->request;
        $request->validate($this->_importRules());
        return $this->resource([], __('messages.import_success'));
    }

    /**
     * @return array
     */
    public function _importRules(): array
    {
        return [];
    }

    /**
     * @throws Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function example()
    {
        $class = $this->getExampleExportClass();
        BaseExampleExport::$locale = static::$exampleLocale;
        BaseExampleExport::$rtl = $this->getExampleExportRtl();
        $fileName = class_basename($this->getBindModel());
        return Excel::download(new $class($this->getBindModel()), str("{$fileName}")->pluralStudly()."Example.xlsx");
    }

    /**
     * @return JsonResponse
     */
    public function exampleUrl()
    {
        $name = str(class_basename($this->getBindModel()))->studly();
        return $this->resource([
            'url' => route("web.{$name}.example"),
        ]);
    }

    /**
     * @return bool
     */
    protected function getExampleExportRtl(): bool
    {
        return app()->getLocale() === 'ar';
    }

    /**
     * @return string
     */
    protected function getExampleExportClass()
    {
        return BaseExampleExport::class;
    }
}

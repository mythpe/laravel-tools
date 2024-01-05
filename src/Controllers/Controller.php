<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Controllers;

use App\Models\User;
use Closure;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Myth\LaravelTools\Traits\BaseController\ApplyQueryTrait;
use Myth\LaravelTools\Traits\BaseController\AttachmentsTrait;
use Myth\LaravelTools\Traits\BaseController\CrudTrait;
use Myth\LaravelTools\Traits\BaseController\EventsTrait;
use Myth\LaravelTools\Traits\BaseController\FilterTrait;
use Myth\LaravelTools\Traits\BaseController\PaginateTrait;
use Myth\LaravelTools\Traits\BaseController\RulesTrait;
use Myth\LaravelTools\Traits\BaseController\SearchTrait;
use Myth\LaravelTools\Traits\BaseController\SortTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    use CrudTrait, EventsTrait, SearchTrait, RulesTrait, SortTrait, PaginateTrait, FilterTrait, AttachmentsTrait, ApplyQueryTrait;

    /**
     * Model Relations
     */
    const RELATIONS = [];

    /** @var Model|null|User */
    public $user;

    /** @var mixed|Request|string|array|null */
    protected $request;

    /**
     * Controller constructor.
     */
    public function __construct()
    {
        $this->request = request();
        method_exists($this, 'iniPaginateRequest') && $this->iniPaginateRequest($this->request);
        $this->middleware(function ($request, Closure $next) {
            $this->user = $request->user();
            return $next($request);
        });
    }

    /**
     * Send API unique response for model
     * Helper
     *
     * @param string|array||\App\Models\BaseModel $model
     * @param string|null $message
     *
     * @return JsonResponse
     */
    protected function resource($model, ?string $message = ''): JsonResponse
    {
        if (is_string($model)) {
            $message = $model;
            $model = null;
        }
        return $this->json([
            "message" => $message,
            "success" => !0,
            "data"    => $model,
        ]);
    }

    /**
     * Send API Unique Response
     *
     * @param array $json response data include message
     * @param int $status
     *
     * @return JsonResponse
     */
    protected function json(array $json = [], int $status = 200): JsonResponse
    {
        ($json['message'] ?? ($json['message'] = ""));
        ($json['data'] ?? ($json['data'] = null));
        $json['success'] = array_key_exists('success', $json) ? $json['success'] : $status == 200;

        $response = response()->json($json, $status);
        try {
            /** For none Json Headers */
            return $response->setEncodingOptions(JSON_UNESCAPED_UNICODE);
        }
        catch (Exception $exception) {
        }
        return $response;
    }

    /**
     * Send API Unique success Message Response
     *
     * @param null|array|string $data
     *
     * @return JsonResponse
     */
    protected function successResponse($data = null): JsonResponse
    {
        $res = [
            "message" => '',
            "success" => !0,
            "data"    => $data,
        ];

        if (is_string($data)) {
            $res['message'] = $data;
            $res['data'] = null;
        }
        if (is_array($data)) {
            $res = array_merge($res, $data);
        }

        return $this->json($res);
    }

    /**
     * Send API Unique Error Message Response
     *
     * @param $message
     * @param array $errors
     * @param array|null $data
     * @param int $status
     *
     * @return JsonResponse
     */
    protected function errorResponse($message, array $errors = [], ?array $data = null, int $status = 422): JsonResponse
    {
        return $this->json([
            'message' => $message,
            "success" => !1,
            "data"    => $data,
            "errors"  => (object) $errors,
        ], $status);
    }

    /**
     * @param $model
     *
     * @return string|null
     */
    protected function requiredRule($model = null): ?string
    {
        $required = 'required';
        if (app()->runningInConsole()) {
            return "required if new";
        }
        $model = $model ?? $this->getBindModel();
        if ($this->isSingle() || $model->exists) {
            return null;
            //return 'nullable';
        }
        return $required;
    }

    /**
     * @return bool
     */
    protected function isSingle(): bool
    {
        return $this->request->has('singleItem');
    }

    /**
     * Get data from request
     *
     * @param $keys
     * @param bool $withCast
     *
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function dataGet($keys, bool $withCast = !0): array
    {
        if (!$withCast) {
            return $this->request->only($keys);
        }
        $result = [];
        $model = new static::$controllerModel;
        foreach (is_array($keys) ? $keys : func_get_args() as $key) {
            $value = $this->request->input($key);
            if (is_null($value) && $model->hasCast($key)) {
                $value = $model->{$key};
            }
            $result[$key] = $value;
        }
        return $result;
    }

    /**
     * Model attribute should not empty value
     *
     * @param $attribute
     *
     * @return string|null
     */
    protected function attrNotNull($attribute): ?string
    {
        $required = 'required';
        if (app()->runningInConsole()) {
            return $required;
        }
        $model = $this->getBindModel();
        $value = $this->request->input($attribute, $model?->{$attribute});
        if (is_null($value)) {
            return $required;
        }
        return null;
    }

    /**
     * Model attribute should not empty value
     *
     * @param $attribute
     *
     * @return null|mixed
     */
    protected function attrValue($attribute)
    {
        if (app()->runningInConsole()) {
            return null;
        }
        return $this->request->input($attribute, $this->getBindModel()?->{$attribute});
    }

    /**
     * @param string $locale
     * @param mixed $default
     * @return mixed|string
     */
    protected function requiredByLocale(string $locale = 'ar', $default = 'nullable')
    {
        return config('app.fallback_locale') == $locale ? 'required' : $default;
    }
}

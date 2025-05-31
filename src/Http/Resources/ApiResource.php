<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Http\Resources;

use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Myth\LaravelTools\Models\BaseModel;

class ApiResource extends JsonResource
{
    /** @var string Request key of static axios request */
    const STATIC_REQUEST_KEY = 'staticRequest';
    /** @var string Type of transform the api resource */
    const API_RESOURCE_CASE_HEADER_KEY = 'X-Api-Trans';
    const API_RESOURCE_CASES_KEY = [
        'camel',
        'snake',
    ];

    /** @var string Request key of items */
    public static string $itemsRequestKey = 'items';
    /** @var string Request key of headers */
    public static string $headerItemsRequestKey = 'headerItems';

    /** @var bool Use auto transform attributes */
    public bool $auto = !0;

    /**
     * @param Countable|Arrayable|array|Collection $values
     * @return Countable|array|Collection|Arrayable
     */
    public static function transformResourceKeys(Countable | Arrayable | array | Collection $values): Countable | array | Collection | Arrayable
    {
        if (!($case = static::apiResourceCase())) {
            return $values;
        }
        return collect($values)->mapWithKeys(fn($value, $key) => [
            Str::{$case}($key) => $value,
        ])->toArray();
    }

    public static function apiResourceCase(): ?string
    {
        $cast = request()->header(static::API_RESOURCE_CASE_HEADER_KEY) ?: null;
        if (in_array($cast, static::API_RESOURCE_CASES_KEY)) {
            return $cast;
        }
        return null;
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        if (is_null($this->resource)) {
            return [];
        }
        if (method_exists($this, 'transformer')) {
            return static::transformResourceKeys($this->transformer($request));
        }
        elseif (is_array($this->resource)) {
            return static::transformResourceKeys($this->resource);
        }
        $id = $this->resource->id;
        $label = $this->resource->name;
        return $this->mainResourceKeys($id, $label, $this->resource->toArray());
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array
     */
    public function transformer(Request $request): array
    {
        return $this->transformModel();
    }

    /**
     *
     * @param string|int|null $id
     * @param string|int|null $label
     * @param array $merge
     * @return array
     */
    public function mainResourceKeys(string | int | null $id = null, string | int | null $label = null, array $merge = []): array
    {
        $main = [
            "id"    => $id,
            "value" => $id,
            "label" => $label,
        ];
        if (config('4myth-tools.transformer.append_text')) {
            $main['text'] = $label;
        }
        return static::transformResourceKeys(array_merge($main, $merge));
    }

    public function isStaticRequest(): bool
    {
        return request()->input(static::STATIC_REQUEST_KEY) ?? false;
    }

    /**
     * @param array $merge
     *
     * @return array
     */
    protected function transformModel(array $merge = []): array
    {
        /** @var BaseModel $model */
        $model = $this->resource;
        $id = $model->id;
        $label = $model->name;
        $description = locale_attribute('description');
        if ($model->isFillable($description) && !array_key_exists('description_to_string', $merge)) {
            $model['description_to_string'] = $model->{$description};
        }

        if ($this->isStaticRequest()) {
            $locales = config('4myth-tools.locales');
            $result = [];
            foreach ($locales as $locale) {
                $attr = locale_attribute('name', $locale);
                if ($model->isFillable($attr)) {
                    $result[$attr] = $model->{$attr};
                }
            }
            if ($model->isFillable('name') && !($result[$k = 'name_'.app()->getLocale()] ?? null)) {
                $result[$k] = $model->name;
            }
            if (method_exists($model, static::STATIC_REQUEST_KEY)) {
                $result = [
                    ...$result,
                    ... $model->{static::STATIC_REQUEST_KEY}(),
                ];
            }
            return $this->mainResourceKeys($id, $label, $result);
        }
        $fillable = $model->only($model->getFillable());
        if (method_exists($model, 'getAppends')) {
            $appends = $model->getAppends();
            $fillable = [
                ...$fillable,
                ... $model->only($appends),
            ];
        }
        $data = [
            ...Arr::except($fillable, $model->getHidden()),
            ...$merge,
        ];
        return $this->mainResourceKeys($id, $label, $data);
    }
}

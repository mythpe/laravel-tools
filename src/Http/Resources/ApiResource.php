<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Myth\LaravelTools\Models\BaseModel;

class ApiResource extends JsonResource
{
    /** @var string Request key of static axios request */
    const STATIC_REQUEST_KEY = 'staticRequest';

    /** @var string Request key of items */
    public static string $itemsRequestKey = 'items';
    /** @var string Request key of headers */
    public static string $headerItemsRequestKey = 'headerItems';

    /** @var bool Use auto transform attributes */
    public bool $auto = !0;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        if (is_null($this->resource)) {
            return [];
        }

        if (method_exists($this, 'transformer')) {
            return $this->transformer($request);
        }

        if (is_array($this->resource)) {
            return $this->resource;
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
        return array_merge($main, $merge);
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
        if (request()->input(static::STATIC_REQUEST_KEY)) {
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
                $result = array_merge($result, $model->{static::STATIC_REQUEST_KEY}());
            }
            return $this->mainResourceKeys($id, $label, $result);
        }
        // if ($this->auto) {
        //     $request = request();
        //     $fdt = $request->input('fdt');
        //     if ($fdt == 'i') {
        //         if (!($columns = $request->input(static::$headerItemsRequestKey))) {
        //             $columns = '*';
        //         }
        //         if (!is_array($columns) && $columns != '*') {
        //             $columns = explode(',', $columns);
        //         }
        //         if ($columns == '*') {
        //             $columns = $model->getFillable();
        //         }
        //         mythAllowHeaders();
        //         // dd($columns, $request->all());
        //         dd($merge);
        //         $merge = array_merge($merge, $model->only($columns));
        //         d($request->keys());
        //         $merge = array_merge($merge, []);
        //     }
        // }
        // return $this->mainResourceKeys($id, $label, $merge);

        $fillable = $model->only($model->getFillable());
        if (method_exists($model, 'getAppends')) {
            $appends = $model->getAppends();
            $fillable = array_merge($fillable, $model->only($appends));
        }
        $data = array_merge(Arr::except($fillable, $model->getHidden()), $merge);
        // ksort($data);
        return $this->mainResourceKeys($id, $label, $data);
    }
}

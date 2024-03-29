<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Http\Resources;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ApiResource extends JsonResource
{

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
        /** @var Model $model */
        $model = $this->resource;
        $id = $model->id;
        $label = $model->name;
        $fillable = $model->only($model->getFillable());
        if (method_exists($model, 'getAppends')) {
            $appends = $model->getAppends();
            $fillable = array_merge($fillable, $model->only($appends));
        }
        $data = array_merge(Arr::except($fillable, $model->getHidden()), $merge);
        ksort($data);
        return $this->mainResourceKeys($id, $label, $data);
    }
}

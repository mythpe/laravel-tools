<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class ApiResource extends JsonResource
{
    /**
     * @param  \Illuminate\Http\Request  $request
     *
     * @return array
     */
    public function toArray($request)
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
        $name = $this->resource->name;
        return array_merge($this->resource->toArray(), [
            "id"    => $id,
            "value" => $id,
            "key"   => (string) $id,
            "text"  => (string) $name,
        ]);
    }

    /**
     * @return array
     */
    public function transformer(): array
    {
        return $this->transformModel();
    }

    /**
     * @param  array  $merge
     *
     * @return array
     */
    protected function transformModel(array $merge = []): array
    {
        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = $this->resource;
        $id = $model->id;
        $name = $model->name;
        $fillable = $model->only($model->getFillable());
        if (method_exists($model, 'getAppends')) {
            $appends = $model->getAppends();
            $fillable = array_merge($fillable, $model->only($appends));
        }

        // $data = array_merge(Arr::except($model->only($model->getFillable()), $model->getHidden()), $merge);
        $data = array_merge(Arr::except($fillable, $model->getHidden()), $merge);

        // $data = array_merge($model->toArray(), $merge);
        if (array_key_exists(($k = locale_attribute()), $data)) {
            $data['name'] = $data[$k];
        }
        if (array_key_exists(($k = locale_attribute('description')), $data)) {
            $data['description'] = $data[$k];
        }
        ksort($data);
        //d($model->getRelations());
        return array_merge([
            "id"    => $id,
            "value" => $id,
            "key"   => (string) $id,
            "text"  => $name,
            "name"  => $name,
        ], $data);
    }
}

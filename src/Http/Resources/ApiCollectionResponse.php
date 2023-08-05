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
use Illuminate\Http\Resources\Json\ResourceCollection;

class ApiCollectionResponse extends ResourceCollection
{
    /**
     * @param $resource
     * @param string|null $collects
     */
    public function __construct($resource, string $collects = null)
    {
        $this->collects = $collects ?: config('4myth-tools.api_resources_class', ApiResource::class);
        parent::__construct($resource);
    }

    /**
     * Transform the resource collection into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        // d($this->collection);
        return [
            'message' => "",
            'success' => true,
            'data'    => $this->collection,
        ];
    }
}

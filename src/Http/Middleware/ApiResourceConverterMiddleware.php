<?php
/*
 * MyTh Ahmed Faiz Copyright © 2026. All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * GitHub: https://github.com/mythpe
 */

namespace Myth\LaravelTools\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Myth\LaravelTools\Http\Resources\ApiResource;

class ApiResourceConverterMiddleware
{
    /**
     * @var array
     */
    protected array $except = [];

    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $except = array_merge($this->except, array_slice(func_get_args(), 2));
        $request->merge($this->process($request->except($except)));
        return $next($request);
    }

    /**
     * @param array $data
     *
     * @return array
     */
    protected function process(array $data)
    {
        return ApiResource::transformResourceKeys($data);
    }
}

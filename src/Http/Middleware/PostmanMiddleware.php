<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PostmanMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
}

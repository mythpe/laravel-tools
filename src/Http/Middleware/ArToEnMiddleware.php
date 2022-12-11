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

class ArToEnMiddleware
{
    /**
     * @var array
     */
    protected $except = [
        'current_password',
        'password',
        'password_confirmation',
    ];

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
        array_walk_recursive(
            $data,
            function(&$value, $key){
                $value = $this->processValue($value, $key);
            }
        );

        return $data;
    }

    /**
     * @param mixed $value
     * @param string $key
     *
     * @return mixed
     */
    protected function processValue($value, $key)
    {
        if(is_string($value)){
            $arabic_eastern = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
            $arabic_western = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

            return str_replace($arabic_eastern, $arabic_western, $value);
        }

        return $value;
    }
}

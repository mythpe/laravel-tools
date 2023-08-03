<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2022 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 */

namespace Myth\LaravelTools\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Str;
use Myth\LaravelTools\Exceptions\NoPermissionException;
use Throwable;

class PermissionMiddleware
{
    /**
     * @param Request $request
     * @param Closure $next
     *
     * @return RedirectResponse|mixed|void|string
     * @throws Throwable
     */
    public function handle(Request $request, Closure $next)
    {
        /** @var User $user */
        $user = $request->user();

        throw_if(!$user, new AuthenticationException);
        /** Check If Administrator Or Support **/
        if ($user->isSupport()) {
            return $next($request);
        }
        /** @var Route $route */
        $route = $request->route();
        $routeName = $route->getName();
        if (!Str::endsWith($routeName, config('4myth-tools.skip_permission_ends_with', []))) {
            $routes = getPermissionRoutes(!0);
            if (in_array($routeName, $routes)) {
                throw_if(!$user->checkPermission($routeName), new NoPermissionException());
            }
        }
        return $next($request);
    }
}

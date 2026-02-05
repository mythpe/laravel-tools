<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2024 All rights reserved.
 * Email: mythpe@gmail.com
 * Mobile: +966590470092
 * Website: https://www.4myth.com
 * Github: https://github.com/mythpe
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
        $permissionName = $route->getName();
        $controller = $route->getController();
        $className = get_class($controller);
        $maps = [
            'clone'      => 'store',
            'example'    => 'import',
            'exampleUrl' => 'import',
            'destroyAll' => 'destroy',
        ];
        if (defined("$className::MAP_PERMISSIONS")) {
            $maps = [...$maps, ...$className::MAP_PERMISSIONS];
        }
        if (method_exists($className, 'getMapPermissions')) {
            $maps = [...$maps, ...$className::getMapPermissions($maps)];
        }
        $mainPermission = Str::beforeLast($permissionName, '.');
        $currentMethod = Str::afterLast($permissionName, '.');
        if (($permissions = ($maps[$permissionName] ?? null))) {
            throw_if(!$user->checkPermission($permissions), new NoPermissionException());
            return $next($request);
        }

        if ($methodMap = ($maps[$currentMethod] ?? null)) {
            $permissionName = [];
            foreach ((array) $methodMap as $method) {
                $permissionName[] = Str::contains($method, ['.']) ? $method : $mainPermission.Str::start($method, '.');
            }
        }

        $skip = config('4myth-tools.skip_permission_ends_with', []);
        $skipMap = [];
        $throw = !0;
        if (defined("$className::NO_PERMISSIONS")) {
            $skipMap = [...$skipMap, ...$className::NO_PERMISSIONS];
        }
        if (method_exists($className, 'noPermissions')) {
            $skipMap = [...$skipMap, ...$className::noPermissions($skipMap)];
        }
        foreach ($skipMap as $value) {
            if (in_array(Str::start($value, '.'), $skip)) {
                continue;
            }
            $skip[] = Str::start($value, '.');
        }
        $skip = array_unique($skip);
        foreach ((array) $permissionName as $value) {
            if (Str::endsWith($value, $skip)) {
                $throw = !1;
                break;
            }
        }
        if ($throw) {
            throw_if(!$user->checkPermission($permissionName), new NoPermissionException());
        }
        return $next($request);
    }
}

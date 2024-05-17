<?php
/*
 * MyTh Ahmed Faiz Copyright © 2016-2023 All rights reserved.
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
        if (defined("$className::MAP_PERMISSIONS")) {
            $maps = $className::MAP_PERMISSIONS;
            foreach ($maps as $key => $value) {
                if (Str::endsWith($permissionName, ".$key")) {
                    $permissionName = str_replace(".$key", ".$value", $permissionName);
                    break;
                }
            }
        }
        $skip = config('4myth-tools.skip_permission_ends_with', []);
        if (defined("$className::NO_PERMISSIONS")) {
            $maps = $className::NO_PERMISSIONS;
            foreach ($maps as $value) {
                if (in_array(".$value", $skip)) {
                    continue;
                }
                $skip[] = Str::start($value, '.');
            }
            $skip = array_unique($skip);
        }
        if (!Str::endsWith($permissionName, $skip)) {
            $routes = getRouterPermissions(!0);
            if (!in_array($permissionName, $routes)) {
                throw_if(!$user->checkPermission($permissionName), new NoPermissionException());
            }
        }
        return $next($request);
    }
}

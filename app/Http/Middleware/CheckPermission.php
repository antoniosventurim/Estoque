<?php

namespace App\Http\Middleware;

use App\Support\PermissionMap;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next): Response
    {
        $permission = PermissionMap::permissionForRoute($request->route()?->getName());

        if ($permission !== null && ! $request->user()?->can($permission)) {
            abort(403);
        }

        return $next($request);
    }
}

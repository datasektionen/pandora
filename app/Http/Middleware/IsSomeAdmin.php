<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Services\PermissionService;

class IsSomeAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            abort(403);
        }

        $permissionService = app(PermissionService::class);
        if (!$permissionService->hasAnyAdminPermission()) {
            abort(403);
        }

        return $next($request);
    }
}

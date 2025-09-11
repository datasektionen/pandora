<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use App\Models\Event;
use App\Services\PermissionService;

class IsAdminForEvent
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

        $event = Event::findOrFail(intval($request->route('id')));
        
        $permissionService = app(PermissionService::class);
        if (!$permissionService->hasPermission(PermissionService::PERMISSION_MANAGE, $event->entity->pls_group)) {
            abort(403);
        }

        return $next($request);
    }
}

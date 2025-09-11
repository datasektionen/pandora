<?php namespace App\Http\Middleware;

use Closure;
use App\Models\Entity;
use Auth;
use App\Services\PermissionService;

/**
 * Checks if user is admin for the entity in the $request->route('id')
 */
class IsAdminForEntity
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

        $entity = Entity::findOrFail(intval($request->route('id')));
        
        $permissionService = app(PermissionService::class);
        if (!$permissionService->hasPermission(PermissionService::PERMISSION_MANAGE, $entity->pls_group)) {
            abort(403);
        }

        return $next($request);
    }
}

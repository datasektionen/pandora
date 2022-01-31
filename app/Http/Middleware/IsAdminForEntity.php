<?php namespace App\Http\Middleware;

use Closure;
use App\Models\Entity;
use Auth;

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
        $entity = Entity::findOrFail(intval($request->route('id')));
        if (!Auth::user()->isAdminFor($entity)) {
            abort(403);
        }
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Entity;
use Auth;
use App\Models\Event;

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
        $event = Event::findOrFail(intval($request->route('id')));
        if (!Auth::user()->isAdminFor($event->entity)) {
            abort(403);
        }
        return $next($request);
    }
}

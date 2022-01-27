<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Entity;
use Auth;
use App\Models\Event;
use Session;

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
        if (count(Session::get('admin', [])) <= 0) {
            abort(403);
        }
        return $next($request);
    }
}

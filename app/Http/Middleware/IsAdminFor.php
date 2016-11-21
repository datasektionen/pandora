<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Entity;
use Auth;

class IsAdminFor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/')->with('error', 'Du måste logga in för att visa den här sidan.');
        }
        $entity = Entity::find(intval($request->route('id')));
        if ($entity === null || !Auth::user()->isAdminFor($entity)) {
            return redirect('/')->with('error', 'Du har inte tillräcklig behörighet för att visa den här sidan.');
        }
        return $next($request);
    }
}

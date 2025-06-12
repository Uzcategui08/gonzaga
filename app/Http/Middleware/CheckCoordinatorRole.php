<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckCoordinatorRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->hasRole('coordinador')) {
            return $next($request);
        }

        return redirect()->back()->with('error', 'No tienes permisos para acceder a esta sección.');
    }
}

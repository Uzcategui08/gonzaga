<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckUserType
{
    public function __construct()
    {
        \Log::info('Middleware CheckUserType - Constructor');
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $type = null): mixed
    {
        if (!auth()->check()) {
            return redirect('/dashboard')->with('error', 'No estás autenticado');
        }

        if (!$type || auth()->user()->hasRole($type)) {
            return $next($request);
        }

        return redirect('/dashboard')->with('error', 'No tienes permisos para acceder a esta sección');
    }
    
}

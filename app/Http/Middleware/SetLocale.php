<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        app()->setLocale('es');

        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252');
        
        return $next($request);
    }
}

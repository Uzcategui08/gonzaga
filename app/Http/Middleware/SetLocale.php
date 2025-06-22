<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $locale = env('APP_LOCALE', 'es');
        
        // Log para verificar que el middleware está funcionando
        Log::info('Setting locale to: ' . $locale);
        
        app()->setLocale($locale);
        setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252');
        
        return $next($request);
    }
}

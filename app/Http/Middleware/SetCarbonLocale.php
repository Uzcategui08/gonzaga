<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

class SetCarbonLocale
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
        Carbon::setLocale('es');
        
        return $next($request);
    }
}
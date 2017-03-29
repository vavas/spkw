<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Support\Facades\Session;

class Brand
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
        if (Auth::check() && Auth::user()->isBrand())
        {
            return $next($request);
        }

        return response(json_encode([
            'status' => false,
            'errors' => 'Invalid route Brand'
        ]), 403);
    }
}

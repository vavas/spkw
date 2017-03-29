<?php

namespace App\Http\Middleware;

use Closure;
use Auth;
use Illuminate\Support\Facades\Session;
use Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Providers\JWTAuthServiceProvider;

class Influencer
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

        $user = JWTAuth::parseToken()->toUser();
        $request->attributes->add(['user' => $user]);
        if ($user && $user->isInfluencer()) {
            return $next($request);
        }

        return response(json_encode([
            'status' => false,
            'errors' => 'Invalid route Influencer'
        ]), 403);


    }
}

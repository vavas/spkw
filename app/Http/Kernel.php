<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Tymon\JWTAuth\Middleware\GetUserFromToken;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
//            \App\Http\Middleware\Influencer::class
            //\Tymon\JWTAuth\Providers\JWTAuthServiceProvider::class,
            /*\App\Http\Middleware\VerifyCsrfToken::class,*/
        ],
        'influencer' => [
            \App\Http\Middleware\Influencer::class
        ],

        'brand' => [
            \App\Http\Middleware\Brand::class
        ],

        'admin' => [
            \App\Http\Middleware\Admin::class
        ],

        'api' => [
            'throttle:60,1',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'jwt.auth' => GetUserFromToken::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'admin' => \App\Http\Middleware\Admin::class,
        'brand' => \App\Http\Middleware\Brand::class,
        'influencer' => \App\Http\Middleware\Influencer::class,
    ];
}

<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    // ...código existente...

    /**
     * The application's route middleware.
     *
     * @var array<string, class-string>
     */
    protected $routeMiddleware = [
        // ...otros middlewares...
        'admin' => \App\Http\Middleware\AdminMiddleware::class,
        'debug.redirect' => \App\Http\Middleware\DebugRedirectMiddleware::class,
    ];
}
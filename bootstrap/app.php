<?php

use App\Exceptions\Handler;
use App\Http\Middleware\DemoGuard;
use Illuminate\Foundation\Configuration\Middleware;
use Pin\Application;

return Application::configure(dirname(__DIR__))
    ->withExceptions(Handler::class)
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies('*')
            ->appendToGroup('api', DemoGuard::class);
    })
    ->create();

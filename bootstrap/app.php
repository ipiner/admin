<?php

use App\Exceptions\Handler;
use App\Http\Middleware\DemoGuard;
use Illuminate\Foundation\Configuration\Middleware;
use Pin\Application;

return Application::configure(dirname(__DIR__))
    ->withExceptions(Handler::class)
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->throttleApi('api', true)
            ->trustProxies('*')
            ->trustHosts()
            ->appendToGroup('api', DemoGuard::class);
    })
    ->create();

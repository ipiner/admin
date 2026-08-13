<?php

use App\Exceptions\Handler;
use App\Http\Middleware\DemoGuard;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Pin\Application;

return Application::configure(dirname(__DIR__))
    ->withExceptions(Handler::class, function (Exceptions $exceptions) {
        $exceptions->shouldRenderJsonWhen(fn () => true);
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->throttleApi('api', true)
            ->trustProxies('*')
            ->trustHosts()
            ->appendToGroup('api', DemoGuard::class);
    })
    ->create();

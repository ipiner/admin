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
    ->withEvents([
        __DIR__.'/../app/Listeners',
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->throttleApi('api', true)
            ->appendToGroup('api', DemoGuard::class);
    })
    ->create();

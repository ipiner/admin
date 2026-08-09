<?php

use App\Exceptions\Handler;
use Illuminate\Foundation\Configuration\Middleware;
use Pin\Application;

return Application::configure(dirname(__DIR__))
    ->withExceptions(Handler::class)
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->trustProxies('*');
    })
    ->create();

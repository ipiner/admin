<?php

use App\Exceptions\Handler;
use Pin\Application;

return Application::configure(dirname(__DIR__))
    ->withExceptions(Handler::class)
    ->withEvents([
        __DIR__.'/../app/Listeners'
    ])
    ->create();

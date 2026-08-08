<?php

use App\Exceptions\Handler;
use Pin\Application;

return Application::configure(dirname(__DIR__))
    ->withExceptions(Handler::class)
    ->create();

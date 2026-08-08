<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Pin\Route\RouteRegistrar;
use Pin\Route\RouteScanner;

Route::middleware('auth')->group(function () {
    RouteRegistrar::register(
        new RouteScanner()->scan([
            app_path('Routes'),
            app_path('Modules'),
        ])
    );
});

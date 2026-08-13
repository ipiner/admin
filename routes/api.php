<?php

declare(strict_types=1);

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use Pin\Route\RouteRegistrar;
use Pin\Route\RouteScanner;

Route::get('/api', [IndexController::class, 'index']);
Route::fallback([IndexController::class, 'fallback']);

Route::middleware('auth')->group(function () {
    RouteRegistrar::register(
        new RouteScanner()->scan([
            app_path('Routes'),
            app_path('Modules'),
        ])
    );
});

<?php

declare(strict_types=1);

namespace App\Routes;

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use Pin\Route\Routable;

/**
 * 基础公共接口路由定义
 */
enum BasicRoute: string implements Routable
{
    use InteractsWithRoute;

    /**
     * 获取 CSRF Token
     */
    case Csrf = 'GET:/api/csrf';

    /**
     * 注册路由
     *
     * 该组路由无需认证即可访问
     */
    public static function registerRoutes(): void
    {
        Route::withoutMiddleware('auth')->group(function () {
            self::Csrf->register([IndexController::class, 'csrf']);
        });
    }
}

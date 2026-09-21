<?php

declare(strict_types=1);

namespace App\Routes;

use App\Http\Controllers\IndexController;
use Illuminate\Support\Facades\Route;
use Override;
use Pin\Route\Attributes\Handler;
use Pin\Route\Routable;

/**
 * 基础路由
 */
enum BasicRoute: string implements Routable
{
    use InteractsWithRoute;

    /**
     * 获取 CSRF Token
     */
    #[Handler([IndexController::class, 'csrf'])]
    case Csrf = 'GET:/api/csrf';

    /**
     * 注册公共路由
     */
    #[Override]
    public static function registerRoutes(): void
    {
        Route::withoutMiddleware('auth')->group(self::addRoutes(...));
    }
}

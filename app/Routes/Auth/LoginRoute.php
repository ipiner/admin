<?php

declare(strict_types=1);

namespace App\Routes\Auth;

use App\Routes\InteractsWithRoute;
use Illuminate\Support\Facades\Route;
use Pin\Password\Middleware\DecodePassword;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 登录接口定义
 */
enum LoginRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('登录')]
    #[Middleware(DecodePassword::class)]
    #[Name('auth.login')]
    case Login = 'POST:/api/auth/login';

    #[Title('退出')]
    case Logout = 'GET:/api/auth/logout';

    /**
     * 注册路由
     */
    public static function registerRoutes(): void
    {
        Route::withoutMiddleware('auth')->group(fn () => self::addRoutes());
    }
}

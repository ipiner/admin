<?php

declare(strict_types=1);

namespace App\Routes\Auth;

use App\Routes\InteractsWithRoute;
use Illuminate\Support\Facades\Route;
use Override;
use Pin\Password\Middleware\DecodePassword;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 登录路由。
 */
enum LoginRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('登录')]
    #[Name('auth.login')]
    #[Middleware(DecodePassword::class)]
    case Login = 'POST:/api/auth/login';

    #[Title('退出')]
    case Logout = 'GET:/api/auth/logout';

    /**
     * 注册公共路由。
     */
    #[Override]
    public static function registerRoutes(): void
    {
        Route::withoutMiddleware('auth')->group(self::addRoutes(...));
    }
}

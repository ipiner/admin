<?php

declare(strict_types=1);

namespace App\Routes;

use App\Modules\Upload\UploadMiddleware;
use Pin\Password\Middleware\DecodePassword;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 帐号接口路由定义
 */
enum AccountRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('获取帐号信息')]
    case Profile = 'GET:/api/account/profile';

    #[Title('修改密码')]
    #[Middleware(DecodePassword::class)]
    case UpdatePassword = 'PUT:/api/account/password';

    #[Title('更新头像')]
    #[Name('account.update.avatar')]
    #[Middleware(UploadMiddleware::class)]
    case UpdateAvatar = 'POST:/api/account/avatar';

    #[Title('更新个人信息')]
    case UpdateProfile = 'PUT:/api/account/profile';
}

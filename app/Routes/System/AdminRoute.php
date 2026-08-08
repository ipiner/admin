<?php

declare(strict_types=1);

namespace App\Routes\System;

use App\Modules\Upload\UploadMiddleware;
use App\Routes\InteractsWithRoute;
use Pin\Password\Middleware\DecodePassword;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 管理员接口路由定义
 */
enum AdminRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('管理员')]
    case Index = 'GET:/api/system/admins';

    #[Title('新增管理员')]
    #[Middleware(DecodePassword::class)]
    case Create = 'POST:/api/system/admins';

    #[Title('编辑管理员')]
    #[Middleware(DecodePassword::class)]
    case Update = 'PUT:/api/system/admins/{id}';

    #[Title('删除管理员')]
    case Delete = 'DELETE:/api/system/admins/{id}';

    #[Name('system.admins.update.avatar')]
    #[Title('编辑管理员头像')]
    #[Middleware(UploadMiddleware::class)]
    case UpdateAvatar = 'POST:/api/system/admins/{id}/avatar';
}

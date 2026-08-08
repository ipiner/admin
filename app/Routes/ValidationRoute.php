<?php

declare(strict_types=1);

namespace App\Routes;

use Pin\Access\Attributes\Access;
use Pin\Crypt\Middleware\Decrypt;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 验证接口路由定义
 */
enum ValidationRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('验证密码')]
    #[Access(false)]
    #[Middleware(Decrypt::class.':password')]
    #[Name('validation.password')]
    case Password = 'POST:/api/validation/password';
}

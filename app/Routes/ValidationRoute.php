<?php

declare(strict_types=1);

namespace App\Routes;

use Pin\Access\Attributes\Access;
use Pin\Route\Attributes\Name;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 验证路由
 */
enum ValidationRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('验证密码')]
    #[Name('validation.password')]
    #[Access(false)]
    case Password = 'POST:/api/validation/password';
}

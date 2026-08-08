<?php

declare(strict_types=1);

namespace App\Routes;

/**
 * 复用 Pin 路由枚举的注册、命名和控制器解析能力。
 */
trait InteractsWithRoute
{
    use \Pin\Access\InteractsWithRoute;
}

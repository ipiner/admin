<?php

declare(strict_types=1);

namespace App\Routes\System;

use App\Routes\InteractsWithRoute;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 角色路由
 */
enum RoleRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('角色')]
    case Index = 'GET:/api/system/roles';

    #[Title('新增角色')]
    case Create = 'POST:/api/system/roles';

    #[Title('编辑角色')]
    case Update = 'PUT:/api/system/roles/{id}';

    #[Title('删除角色')]
    case Delete = 'DELETE:/api/system/roles/{id}';

    #[Title('角色下拉框选择器')]
    case Selector = 'GET:/api/system/roles/selector';
}

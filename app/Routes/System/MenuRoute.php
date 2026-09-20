<?php

declare(strict_types=1);

namespace App\Routes\System;

use App\Routes\InteractsWithRoute;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 菜单路由。
 */
enum MenuRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('菜单')]
    case Index = 'GET:/api/system/menus';

    #[Title('新增菜单')]
    case Create = 'POST:/api/system/menus';

    #[Title('编辑菜单')]
    case Update = 'PUT:/api/system/menus/{id}';

    #[Title('更新菜单启用状态')]
    case UpdateEnabled = 'PUT:/api/system/menus/{id}/enabled';

    #[Title('更新菜单可见状态')]
    case UpdateVisible = 'PUT:/api/system/menus/{id}/visible';

    #[Title('删除菜单')]
    case Delete = 'DELETE:/api/system/menus/{id}';

    /**
     * 菜单与角色权限选择器。
     */
    #[Title('菜单下拉框选择器')]
    case Selector = 'GET:/api/system/menus/selector';

    #[Title('可用菜单编码')]
    case AvailableCodes = 'GET:/api/system/menus/codes';
}

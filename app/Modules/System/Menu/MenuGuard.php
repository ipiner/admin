<?php

declare(strict_types=1);

namespace App\Modules\System\Menu;

use App\Models\System\Menu;
use App\Routes\System\MenuRoute;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 菜单守卫
 *
 * 菜单业务相关的权限约束和特殊菜单保护逻辑
 */
class MenuGuard
{
    /**
     * 是否允许删除
     *
     * @throws Exception
     */
    public static function ensureDeletable(Menu $menu): void
    {
        if ($menu->code === MenuRoute::Index->name()) {
            Errors::DeleteFailed->throw("禁止删除 [{$menu->name}]");
        }
    }

    /**
     * 是否允许变更启用状态
     *
     * @throws Exception
     */
    public static function ensureEnabledStatusChangeAllowed(Menu $menu, ?int $nextEnabled): void
    {
        if ($nextEnabled === null) {
            return;
        }

        // 指定菜单不可禁用
        if (
            $nextEnabled === Menu::DISABLED
            && in_array($menu->code, static::enabledAlways())
        ) {
            Errors::UpdateFailed->throw("[{$menu->name}] 不可禁用");
        }

        // 系统状态不可修改
        if (
            $menu->enabled === Menu::SYSTEM_ENABLED
            && $nextEnabled !== Menu::SYSTEM_ENABLED
        ) {
            Errors::UpdateFailed->throw("禁止修改 [{$menu->name}] 启用状态");
        }
    }

    /**
     * 返回系统强制保持启用的菜单编码。
     */
    private static function enabledAlways(): array
    {
        return [MenuRoute::Index->name()];
    }
}

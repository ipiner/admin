<?php

declare(strict_types=1);

namespace App\Modules\System\Menu;

use App\Models\System\Menu;
use App\Routes\System\MenuRoute;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 菜单操作权限。
 */
class MenuGuard
{
    /**
     * 检查删除权限。
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
     * 检查启用状态变更。
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
            && in_array($menu->code, static::alwaysEnabledCodes(), true)
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
     * 不可禁用的菜单编码。
     *
     * @return list<string>
     */
    protected static function alwaysEnabledCodes(): array
    {
        return [MenuRoute::Index->name()];
    }
}

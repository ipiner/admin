<?php

declare(strict_types=1);

namespace App\Modules\System\Admin;

use App\Models\System\Admin;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 管理员操作权限
 */
class AdminGuard
{
    /**
     * 检查修改权限
     *
     * @throws Exception
     */
    public static function ensureUpdatable(Admin $admin): void
    {
        if ($admin->isAdministrator() && ! auth()->user()->isAdministrator()) {
            throw Errors::UpdateFailed->exception('禁止修改该管理员')->withStatusCode(403);
        }
    }

    /**
     * 检查启用状态变更
     *
     * @throws Exception
     */
    public static function ensureEnabledStatusChangeAllowed(
        Admin $admin,
        ?int $nextEnabled,
    ): void {
        if ($nextEnabled !== Admin::DISABLED || ! $admin->isAdministrator()) {
            return;
        }

        Errors::UpdateFailed->throw('禁止停用超级管理员');
    }

    /**
     * 检查删除权限
     *
     * @throws Exception
     */
    public static function ensureDeletable(Admin $admin): void
    {
        if ($admin->isAdministrator()) {
            throw Errors::DeleteFailed->exception('禁止删除该管理员')->withStatusCode(403);
        }
    }
}

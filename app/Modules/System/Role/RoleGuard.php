<?php

declare(strict_types=1);

namespace App\Modules\System\Role;

use App\Models\System\Role;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 角色操作权限。
 */
class RoleGuard
{
    /**
     * 检查修改权限。
     *
     * @throws Exception
     */
    public static function ensureUpdatable(Role $role): void
    {
        if ($role->isSuperRole() && ! auth()->user()->isAdministrator()) {
            throw Errors::UpdateFailed->exception('禁止修改该角色')->withStatusCode(403);
        }
    }

    /**
     * 检查删除权限。
     *
     * @throws Exception
     */
    public static function ensureDeletable(Role $role): void
    {
        if ($role->isSuperRole()) {
            throw Errors::DeleteFailed->exception('禁止删除该角色')->withStatusCode(403);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\System\Role;

use App\Models\System\Role;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 角色守卫
 *
 * 角色业务相关的权限约束和特殊角色保护逻辑
 */
class RoleGuard
{
    /**
     * 角色是否允许被更新
     *
     * @throws Exception
     */
    public static function ensureUpdatable(Role $model): void
    {
        if ($model->isSuperRole() && ! auth()->user()->isAdministrator()) {
            throw Errors::UpdateFailed->exception('禁止修改该角色')->withStatusCode(403);
        }
    }

    /**
     * 角色是否允许被删除
     *
     * @throws Exception
     */
    public static function ensureDeletable(Role $model): void
    {
        if ($model->isSuperRole()) {
            throw Errors::DeleteFailed->exception('禁止删除该角色')->withStatusCode(403);
        }
    }
}

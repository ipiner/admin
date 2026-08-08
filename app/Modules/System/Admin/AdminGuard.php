<?php

declare(strict_types=1);

namespace App\Modules\System\Admin;

use App\Models\System\Admin;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 管理员守卫
 *
 * 管理员业务相关的权限约束和特殊管理员保护逻辑
 */
class AdminGuard
{
    /**
     * 管理员是否允许被更新
     *
     * @throws Exception
     */
    public static function ensureUpdatable(Admin $model): void
    {
        if ($model->isAdministrator() && ! auth()->user()->isAdministrator()) {
            throw Errors::UpdateFailed->exception('禁止修改该管理员')->withStatusCode(403);
        }
    }

    /**
     * 管理员是否允许被删除
     *
     * @throws Exception
     */
    public static function ensureDeletable(Admin $model): void
    {
        if ($model->isAdministrator()) {
            throw Errors::DeleteFailed->exception('禁止删除该管理员')->withStatusCode(403);
        }
    }
}

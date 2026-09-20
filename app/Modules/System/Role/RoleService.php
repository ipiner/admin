<?php

declare(strict_types=1);

namespace App\Modules\System\Role;

use App\Models\System\Admin;
use App\Models\System\Role;
use Override;
use Pin\Services\ModelService;

/**
 * 角色服务。
 *
 * @extends ModelService<Role>
 */
class RoleService extends ModelService
{
    /**
     * 清理角色成员的权限缓存。
     */
    public function flushAccess(Role $role): void
    {
        $userIds = $role->getConnection()->table('role_admins')
            ->where('role_id', $role->id)
            ->pluck('uid');
        $accessProvider = config('pin.access.access_provider');

        foreach ($userIds as $id) {
            $accessProvider::flushAccess(new Admin(['id' => $id]));
        }
    }

    /**
     * @param  Role  $model
     */
    #[Override]
    protected function updating($model, array &$data): void
    {
        RoleGuard::ensureUpdatable($model);
        parent::updating($model, $data);
    }

    /**
     * @param  Role  $model
     */
    #[Override]
    protected function deleting($model): void
    {
        RoleGuard::ensureDeletable($model);
        parent::deleting($model);
    }

    /**
     * @param  Role  $model
     */
    #[Override]
    protected function deleted($model): void
    {
        parent::deleted($model);

        $this->flushAccess($model);
    }
}

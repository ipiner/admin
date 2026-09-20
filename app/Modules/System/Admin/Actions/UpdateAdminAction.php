<?php

declare(strict_types=1);

namespace App\Modules\System\Admin\Actions;

use App\Models\System\Admin;
use App\Models\System\Role;
use Pin\Services\Results\UpdateResult;

/**
 * 更新管理员。
 */
class UpdateAdminAction extends AdminAction
{
    /**
     * 更新管理员。
     */
    public function handle(int $id): UpdateResult
    {
        $admin = Admin::findOrFail($id);
        $data = $this->validated();
        $roleIds = $this->extractRoleIds($data);

        $this->normalizePassword($admin, $data);

        return $this->service->update(
            $admin,
            $data,
            fn (Admin $admin) => $this->syncRoles($admin, $roleIds),
        );
    }

    /**
     * 处理密码。
     */
    protected function normalizePassword(Admin $admin, array &$data): void
    {
        if (empty($data['password'])) {
            unset($data['password']);

            return;
        }

        $data['password'] = $admin->hashPassword($data['password']);
    }

    /**
     * 更新验证规则。
     */
    public function rules(): array
    {
        return [
            ...$this->basicRules(),

            // 密码（加密传输），不修改密码留空
            'password' => 'nullable|string|fake:password',

            // 数据版本号
            'v' => 'required|integer',
        ];
    }

    /**
     * 同步角色。
     */
    protected function syncRoles(Admin $admin, array $roleIds): void
    {
        if ($admin->isAdministrator()) {
            return;
        }

        $old = $admin->roles()->orderBy('role_id')->pluck('name')->join("\n");
        $changes = $admin->roles()->sync($roleIds);

        if (! $changes['attached'] && ! $changes['detached']) {
            return;
        }

        $admin->unsetRelation('roles');
        $accessProvider = config('pin.access.access_provider');
        $accessProvider::flushAccess($admin);

        $new = Role::findMany($roleIds)->sortBy('id')->pluck('name')->join("\n");
        $admin->mergeOperationChanges(
            ['roles' => "\n".$old.($old ? "\n" : '')],
            ['roles' => "\n".$new.($new ? "\n" : '')]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\System\Admin\Actions;

use App\Models\System\Admin;
use App\Models\System\Role;
use Pin\Services\Results\UpdateResult;

/**
 * 更新管理员资料、密码和角色关系。
 */
class UpdateAdminAction extends AdminAction
{
    /**
     * 执行管理员更新流程。
     */
    public function handle(int $id): UpdateResult
    {
        $admin = Admin::findOrFail($id);
        $data = $this->validated();
        $roleIds = $this->extractRoleIds($data, $admin->isAdministrator());

        $this->normalizePassword($admin, $data);

        return $this->service->update(
            $admin,
            $data,
            fn (Admin $item) => $this->syncRoles($admin, $roleIds),
        );
    }

    /**
     * 空密码表示不修改；非空密码在保存前重新哈希。
     */
    protected function normalizePassword(Admin $admin, array &$data): void
    {
        if (empty($data['password'])) {
            unset($data['password']);
        } else {
            $data['password'] = $admin->hashPassword($data['password']);
        }
    }

    /**
     * 更新管理员请求验证规则。
     */
    protected function rules(): array
    {
        return [
            ...$this->basicRules(),

            // 密码（加密传输），不修改密码留空
            'password' => 'nullable:50|fake:password',

            // 数据版本号
            'v' => 'required|integer',
        ];
    }

    /**
     * 同步角色关系，并把角色变更写入操作日志。
     */
    protected function syncRoles(Admin $item, array $roleIds): void
    {
        if ($item->isAdministrator()) {
            return;
        }

        $roleIds = in_array(Role::SUPER_ROLE_ID, $roleIds) ? [Role::SUPER_ROLE_ID] : $roleIds;
        $old = $item->roles()->orderBy('role_id')->pluck('name')->join("\n");
        $result = $item->roles()->sync($roleIds);

        if (empty($result['attached']) && empty($result['detached'])) {
            return;
        }

        $new = Role::findMany($roleIds)->sortBy('id')->pluck('name')->join("\n");
        $item->mergeOperationChanges(
            ['roles' => "\n".$old.($old ? "\n" : '')],
            ['roles' => "\n".$new.($new ? "\n" : '')]
        );
    }
}

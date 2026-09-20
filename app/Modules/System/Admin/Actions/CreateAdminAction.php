<?php

declare(strict_types=1);

namespace App\Modules\System\Admin\Actions;

use App\Models\System\Admin;
use App\Models\System\Role;
use Pin\Services\Results\CreateResult;

/**
 * 创建管理员。
 */
class CreateAdminAction extends AdminAction
{
    /**
     * 创建管理员。
     */
    public function handle(): CreateResult
    {
        $data = $this->validated();
        $roleIds = $this->extractRoleIds($data);

        return $this->service->create(
            $data,
            fn (Admin $admin) => $this->attachRoles($admin, $roleIds),
        );
    }

    /**
     * 分配角色。
     */
    protected function attachRoles(Admin $admin, array $roleIds): void
    {
        if (! $roleIds) {
            return;
        }

        $admin->roles()->attach($roleIds);
        $roles = Role::findMany($roleIds)->sortBy('id')->pluck('name')->join("\n");
        $admin->mergeOperationChanges([], ['roles' => "\n".$roles."\n"]);
    }

    /**
     * 创建验证规则。
     */
    public function rules(): array
    {
        // 展开规则供 Scramble 解析。
        return [...$this->basicRules()];
    }
}

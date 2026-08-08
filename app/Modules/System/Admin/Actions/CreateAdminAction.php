<?php

declare(strict_types=1);

namespace App\Modules\System\Admin\Actions;

use App\Models\System\Admin;
use App\Models\System\Role;
use Pin\Services\Results\CreateResult;

/**
 * 创建管理员并同步初始角色。
 */
class CreateAdminAction extends AdminAction
{
    /**
     * 执行管理员创建流程。
     */
    public function handle(): CreateResult
    {
        $data = $this->validated();
        $roleIds = $this->extractRoleIds($data);

        return $this->service->create(
            $data,
            fn (Admin $item) => $this->attachRoles($item, $roleIds),
        );
    }

    /**
     * 创建后挂载角色，并补充操作日志变更内容。
     */
    protected function attachRoles(Admin $item, array $roleIds): void
    {
        $roleIds = in_array(Role::SUPER_ROLE_ID, $roleIds) ? [Role::SUPER_ROLE_ID] : $roleIds;
        if (! $roleIds) {
            return;
        }

        $item->roles()->attach($roleIds);
        $roles = Role::findMany($roleIds)->sortBy('id')->pluck('name')->join("\n");
        $item->mergeOperationChanges([], ['roles' => "\n".$roles."\n"]);
    }

    /**
     * 创建管理员请求验证规则。
     */
    protected function rules(): array
    {
        // ...展开，Scramble可识别
        return [...$this->basicRules()];
    }
}

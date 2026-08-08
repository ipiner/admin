<?php

declare(strict_types=1);

namespace App\Modules\System\Role\Actions;

use App\Models\System\Role;
use App\Modules\System\Role\MenusMustExistRule;
use App\Modules\System\Role\RoleService;
use Pin\Actions\Action;
use Pin\Validation\Rules\Unique;

/**
 * 角色创建和更新动作共享的验证规则。
 */
class RoleAction extends Action
{
    /**
     * 注入角色写入服务
     */
    public function __construct(protected RoleService $service)
    {
    }

    /**
     * 角色创建和更新共享的基础验证规则。
     */
    protected function basicRules(): array
    {
        return [
            // 角色名称
            'name' => [
                'required',
                'string',
                'unique' => new Unique(Role::class)->ignore(
                    (int) $this->context->get('id')
                ),
            ],

            // 备注
            'remark' => 'nullable|string',

            /**
             * 菜单权限
             *
             * @example []
             */
            'menus' => [
                'nullable',
                'array',
                new MenusMustExistRule(),
            ],
            'menus.*' => 'integer',
        ];
    }

    /**
     * 从写入数据中剥离菜单字段，超级角色不通过表单维护菜单权限。
     */
    protected function extractMenuIds(array &$data, bool $isSuper = false): array
    {
        $menuIds = $data['menus'] ?? [];
        unset($data['menus']);

        return $isSuper ? [] : $menuIds;
    }
}

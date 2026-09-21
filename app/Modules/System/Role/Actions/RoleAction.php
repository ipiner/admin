<?php

declare(strict_types=1);

namespace App\Modules\System\Role\Actions;

use App\Models\System\Role;
use App\Modules\System\Role\MenusMustExistRule;
use App\Modules\System\Role\RoleService;
use Pin\Action\Action;
use Pin\Validation\Rules\Unique;

/**
 * 角色写入操作
 */
class RoleAction extends Action
{
    public function __construct(protected RoleService $service)
    {
    }

    /**
     * 基础验证规则
     */
    protected function basicRules(): array
    {
        return [
            // 角色名称
            'name' => [
                'bail',
                'required',
                'string',
                'unique' => new Unique(Role::class)->ignore(
                    (int) $this->context('id')
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
                'bail',
                'nullable',
                'array',
                new MenusMustExistRule(),
            ],
            'menus.*' => 'integer',
        ];
    }

    /**
     * 提取菜单 ID
     */
    protected function extractMenuIds(array &$data): array
    {
        $menuIds = $data['menus'] ?? [];
        unset($data['menus']);

        return array_values(array_unique($menuIds));
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\System\Admin\Actions;

use App\Models\System\Admin;
use App\Models\System\Role;
use App\Modules\System\Admin\AdminService;
use App\Modules\System\Admin\RoleAssignableRule;
use Pin\Action\Action;
use Pin\Captcha\Rule;
use Pin\Validation\Rules\Unique;

/**
 * 管理员写入操作。
 */
class AdminAction extends Action
{
    public function __construct(protected AdminService $service = new AdminService())
    {
    }

    /**
     * 基础验证规则。
     */
    protected function basicRules(): array
    {
        return [
            // 用户名
            'username' => [
                'bail',
                'required',
                'string',
                'unique' => new Unique(Admin::class)->ignore(
                    (int) $this->context('id')
                ),
            ],

            // 姓名
            'realname' => 'required|string|fake:firstname',

            /**
             * 密码（加密传输）
             *
             * @example plain:123456
             */
            'password' => 'required|string|fake:password',

            /**
             * 验证码验证规则
             *
             * @example rev
             */
            'captcha_rule' => [
                'bail',
                'nullable',
                'string',
                static fn ($attribute, $value) => Rule::parse($value),
                'fake:in,normal,rev',
            ],

            /**
             * 角色id
             *
             * @example []
             */
            'roles' => ['bail', 'nullable', 'array', new RoleAssignableRule()],
            'roles.*' => 'integer',
        ];
    }

    /**
     * 提取角色 ID。
     */
    protected function extractRoleIds(array &$data): array
    {
        $roleIds = $data['roles'] ?? [];
        unset($data['roles']);

        return in_array(Role::SUPER_ROLE_ID, $roleIds)
            ? [Role::SUPER_ROLE_ID]
            : array_values(array_unique($roleIds));
    }
}

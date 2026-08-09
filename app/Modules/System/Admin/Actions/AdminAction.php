<?php

declare(strict_types=1);

namespace App\Modules\System\Admin\Actions;

use App\Models\System\Admin;
use App\Modules\System\Admin\AdminService;
use App\Modules\System\Admin\RoleAssignableRule;
use Pin\Action\Action;
use Pin\Captcha\Rule;
use Pin\Validation\Rules\Unique;

/**
 * 管理员创建和更新动作共享的验证规则。
 */
class AdminAction extends Action
{
    public function __construct(protected AdminService $service)
    {
    }

    /**
     * 管理员创建和更新共享的基础验证规则。
     */
    protected function basicRules(): array
    {
        return [
            // 用户名
            'username' => [
                'required',
                'string',
                'unique' => new Unique(Admin::class)->ignore(
                    (int) $this->context->get('id')
                ),
            ],

            // 姓名
            'realname' => 'required|fake:firstname',

            /**
             * 密码（加密传输）
             *
             * 非生产环境下可以使用 `plain:123456` 格式的明文密码
             *
             * @example plain:123456
             */
            'password' => 'required|fake:password',

            /**
             * 验证码验证规则
             *
             * @example rev
             */
            'captcha_rule' => [
                'nullable',
                fn ($attribute, $value) => Rule::parse($value),
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
     * 从写入数据中剥离角色字段，超级管理员不允许通过表单重设角色。
     */
    protected function extractRoleIds(array &$data, bool $isSuper = false): array
    {
        $roleIds = $data['roles'] ?? [];
        unset($data['roles']);

        return $isSuper ? [] : $roleIds;
    }
}

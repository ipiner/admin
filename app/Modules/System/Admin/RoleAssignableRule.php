<?php

declare(strict_types=1);

namespace App\Modules\System\Admin;

use App\Models\System\Role;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 校验管理员角色是否存在且当前用户有权分配。
 */
class RoleAssignableRule implements ValidationRule
{
    /**
     * {@inheritDoc}
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $roles = Role::findMany($value);

        foreach ($value as $id) {
            $role = $roles->get($id);

            if (! $role) {
                $fail("角色 [{$id}] 不存在");
            } elseif (! $this->canAssignRole($role)) {
                $fail("无权限分配角色 [{$role->name}]");
            }
        }
    }

    /**
     * 普通管理员不能把超级角色分配给其他账号。
     */
    protected function canAssignRole(Role $role): bool
    {
        return ! $role->isSuperRole() || auth()->user()->hasAllAccess();
    }
}

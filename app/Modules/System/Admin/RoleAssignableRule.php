<?php

declare(strict_types=1);

namespace App\Modules\System\Admin;

use App\Models\System\Role;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;

/**
 * 角色分配校验。
 */
class RoleAssignableRule implements ValidationRule
{
    /**
     * 校验角色。
     */
    #[Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 角色格式由 roles.* 校验。
        $roleIds = array_filter(
            $value,
            static fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false
        );
        if (! $roleIds) {
            return;
        }

        $roles = Role::findMany($roleIds);

        foreach ($roleIds as $id) {
            $role = $roles->get((int) $id);

            if (! $role) {
                $fail("角色 [{$id}] 不存在");
            } elseif (! $this->canAssignRole($role)) {
                $fail("无权限分配角色 [{$role->name}]");
            }
        }
    }

    /**
     * 是否允许分配角色。
     */
    protected function canAssignRole(Role $role): bool
    {
        return ! $role->isSuperRole() || auth()->user()->hasAllAccess();
    }
}

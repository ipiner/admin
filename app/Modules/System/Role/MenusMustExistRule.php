<?php

declare(strict_types=1);

namespace App\Modules\System\Role;

use App\Models\System\Menu;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 校验角色菜单权限列表中的菜单是否全部存在。
 */
class MenusMustExistRule implements ValidationRule
{
    /**
     * {@inheritDoc}
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $value = (array) $value;
        $exists = Menu::findMany($value)->keys()->toArray();
        $diff = array_diff($value, $exists);

        if ($diff) {
            $fail('菜单 ['.implode(',', $diff).'] 不存在');
        }
    }
}

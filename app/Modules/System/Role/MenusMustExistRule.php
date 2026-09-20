<?php

declare(strict_types=1);

namespace App\Modules\System\Role;

use App\Models\System\Menu;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Override;

/**
 * 菜单存在性校验。
 */
class MenusMustExistRule implements ValidationRule
{
    /**
     * 校验菜单。
     */
    #[Override]
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // 菜单格式由 menus.* 校验。
        $menuIds = array_filter(
            $value,
            static fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false
        );
        if (! $menuIds) {
            return;
        }

        $existingIds = Menu::findMany($menuIds)->pluck('id')->all();
        $missingIds = array_diff($menuIds, $existingIds);

        if ($missingIds) {
            $fail('菜单 ['.implode(',', $missingIds).'] 不存在');
        }
    }
}

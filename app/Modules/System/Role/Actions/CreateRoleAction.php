<?php

declare(strict_types=1);

namespace App\Modules\System\Role\Actions;

use App\Models\System\Menu;
use App\Models\System\Role;
use Pin\Services\Results\CreateResult;

/**
 * 创建角色。
 */
class CreateRoleAction extends RoleAction
{
    /**
     * 创建角色。
     */
    public function handle(): CreateResult
    {
        $data = $this->validated();
        $menuIds = $this->extractMenuIds($data);

        return $this->service->create(
            $data,
            fn (Role $role) => $this->attachMenus($role, $menuIds),
        );
    }

    /**
     * 分配菜单。
     */
    protected function attachMenus(Role $role, array $menuIds): void
    {
        if (! $menuIds) {
            return;
        }

        $role->menus()->attach($menuIds);
        $menus = Menu::findMany($menuIds)->sortBy('id')->pluck('name')->join("\n");
        $role->mergeOperationChanges([], ['menus' => "\n".$menus."\n"]);
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

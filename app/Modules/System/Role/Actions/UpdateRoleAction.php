<?php

declare(strict_types=1);

namespace App\Modules\System\Role\Actions;

use App\Models\System\Menu;
use App\Models\System\Role;
use Pin\Services\Results\UpdateResult;

/**
 * 更新角色
 */
class UpdateRoleAction extends RoleAction
{
    /**
     * 更新角色
     */
    public function handle(int $id): UpdateResult
    {
        $role = Role::findOrFail($id);
        $data = $this->validated();
        $menuIds = $this->extractMenuIds($data);

        return $this->service->update(
            $role,
            $data,
            fn (Role $role) => $this->syncMenus($role, $menuIds),
        );
    }

    /**
     * 更新验证规则
     */
    public function rules(): array
    {
        return [
            ...$this->basicRules(),

            // 数据版本号
            'v' => 'required|integer',
        ];
    }

    /**
     * 同步菜单
     */
    protected function syncMenus(Role $role, array $menuIds): void
    {
        if ($role->isSuperRole()) {
            return;
        }

        $old = $role->menus()->orderBy('menu_id')->pluck('name')->join("\n");
        $changes = $role->menus()->sync($menuIds);

        if (! $changes['attached'] && ! $changes['detached']) {
            return;
        }

        $role->unsetRelation('menus');
        $this->service->flushAccess($role);

        $new = Menu::findMany($menuIds)->sortBy('id')->pluck('name')->join("\n");
        $role->mergeOperationChanges(
            ['menus' => "\n".$old.($old ? "\n" : '')],
            ['menus' => "\n".$new.($new ? "\n" : '')]
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\System\Role\Actions;

use App\Models\System\Menu;
use App\Models\System\Role;
use Pin\Services\Results\UpdateResult;

/**
 * 更新角色资料和菜单权限。
 */
class UpdateRoleAction extends RoleAction
{
    /**
     * 执行角色更新流程。
     */
    public function handle(int $id): UpdateResult
    {
        $item = Role::findOrFail($id);
        $data = $this->validated();
        $menuIds = $this->extractMenuIds($data, $item->isSuperRole());

        return $this->service->update(
            $item,
            $data,
            fn (Role $item) => $this->syncMenus($item, $menuIds),
        );
    }

    /**
     * 更新角色请求验证规则。
     */
    protected function rules(): array
    {
        return [
            ...$this->basicRules(),

            // 数据版本号
            'v' => 'required|integer',
        ];
    }

    /**
     * 同步菜单权限，并把权限变更写入操作日志。
     */
    protected function syncMenus(Role $item, array $menuIds): array
    {
        if ($item->isSuperRole()) {
            return [];
        }

        $old = $item->menus()->orderBy('menu_id')->pluck('name')->join("\n");
        $result = $item->menus()->sync($menuIds);

        if (empty($result['attached']) && empty($result['detached'])) {
            return $result;
        }

        $new = Menu::findMany($menuIds)->sortBy('id')->pluck('name')->join("\n");
        $item->mergeOperationChanges(
            ['menus' => "\n".$old.($old ? "\n" : '')],
            ['menus' => "\n".$new.($new ? "\n" : '')]
        );

        return $result;
    }
}

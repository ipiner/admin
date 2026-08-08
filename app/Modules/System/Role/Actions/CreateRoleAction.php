<?php

declare(strict_types=1);

namespace App\Modules\System\Role\Actions;

use App\Models\System\Menu;
use App\Models\System\Role;
use Pin\Services\Results\CreateResult;

/**
 * 创建角色并挂载初始菜单权限。
 */
class CreateRoleAction extends RoleAction
{
    /**
     * 执行角色创建流程。
     */
    public function handle(): CreateResult
    {
        $data = $this->validated();
        $menuIds = $this->extractMenuIds($data);

        return $this->service->create(
            $data,
            fn (Role $item) => $this->attachMenus($item, $menuIds),
        );
    }

    /**
     * 创建后挂载菜单权限，并补充操作日志变更内容。
     */
    protected function attachMenus(Role $item, array $menuIds): void
    {
        if (! $menuIds) {
            return;
        }

        $item->menus()->attach($menuIds);
        $menus = Menu::findMany($menuIds)->sortBy('id')->pluck('name')->join("\n");
        $item->mergeOperationChanges([], ['menus' => "\n".$menus."\n"]);
    }

    /**
     * 创建角色请求验证规则。
     */
    protected function rules(): array
    {
        // ...展开，Scramble可识别
        return [...$this->basicRules()];
    }
}

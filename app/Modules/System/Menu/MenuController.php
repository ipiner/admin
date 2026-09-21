<?php

declare(strict_types=1);

namespace App\Modules\System\Menu;

use App\Http\Controllers\Controller;
use App\Models\System\Menu;
use App\Modules\System\Menu\Actions\AvailableCodesAction;
use App\Modules\System\Menu\Actions\CreateMenuAction;
use App\Modules\System\Menu\Actions\UpdateEnabledAction;
use App\Modules\System\Menu\Actions\UpdateMenuAction;
use App\Modules\System\Menu\Actions\UpdateVisibleAction;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Pagination\Pagination;
use Pin\Scramble\Created;
use Pin\Scramble\Deleted;
use Pin\Scramble\Updated;
use Pin\Validation\QueryableRules;

/**
 * 菜单管理
 */
#[Group('系统 / 菜单')]
class MenuController extends Controller
{
    /**
     * 新增菜单
     *
     * @return ApiResponse<Created>
     */
    public function create(CreateMenuAction $action): ApiResponse
    {
        return $this->success($action->handle());
    }

    /**
     * 删除菜单
     *
     * @param  int  $id  菜单id
     * @return ApiResponse<Deleted>
     */
    public function delete(MenuService $service, int $id): ApiResponse
    {
        return $this->success($service->delete($id));
    }

    /**
     * 菜单列表
     *
     * @return ApiResponse<Pagination<Menu>>
     */
    public function index(Request $request, MenuService $service): ApiResponse
    {
        $rules = [
            // 是否分页
            'paging' => 'nullable|in:0,1',

            /**
             * 关键字，支持查询 `id` / `菜单名称`
             *
             * @example 1 / 用户
             */
            'q' => QueryableRules::ns('id,name'),

            // 菜单编码
            'code' => QueryableRules::like(),

            // 前端路由
            'route' => QueryableRules::like(),
        ];
        $request->validate($rules);
        $paging = $request->boolean('paging');

        return $this->success(
            $service->context('paging', $paging)->pagination($paging ? $rules : null)
        );
    }

    /**
     * 菜单下拉框选择器
     *
     * @return ApiResponse<array{
     *     label: string,
     *     value: int,
     *     type: string,
     *     pid: int,
     *   }[]>
     */
    public function selector(): ApiResponse
    {
        $options = Menu::findAll()->values()->map(static fn (Menu $menu): array => [
            'label' => $menu->name,
            'value' => $menu->id,
            'type' => $menu->type,
            'pid' => $menu->pid,
        ]);

        return $this->success($options);
    }

    /**
     * 可用菜单编码列表
     *
     * @return ApiResponse<array{
     *     label: string,
     *     value: string,
     *     name: string
     *   }[]>
     */
    public function availableCodes(AvailableCodesAction $action): ApiResponse
    {
        return $this->success($action->handle());
    }

    /**
     * 更新菜单
     *
     * @param  int  $id  菜单id
     * @return ApiResponse<Updated>
     */
    public function update(UpdateMenuAction $action, int $id): ApiResponse
    {
        return $this->success($action->handle($id));
    }

    /**
     * 更新启用状态
     *
     * @param  int  $id  菜单id
     * @return ApiResponse<Updated>
     */
    public function updateEnabled(UpdateEnabledAction $action, int $id): ApiResponse
    {
        return $this->success($action->handle($id));
    }

    /**
     * 更新显示状态
     *
     * @param  int  $id  菜单id
     * @return ApiResponse<Updated>
     */
    public function updateVisible(UpdateVisibleAction $action, int $id): ApiResponse
    {
        return $this->success($action->handle($id));
    }
}

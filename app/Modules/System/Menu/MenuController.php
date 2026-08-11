<?php

declare(strict_types=1);

namespace App\Modules\System\Menu;

use App\Http\Controllers\Controller;
use App\Models\System\Menu;
use App\Modules\System\Menu\Actions\AvailableCodesAction;
use App\Modules\System\Menu\Actions\CreateMenuAction;
use App\Modules\System\Menu\Actions\MenuAction;
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
use Pin\Validation\QueryableRules as Queryable;

/**
 * 菜单树的增删改查和选择器接口。
 */
#[Group('系统 / 菜单')]
class MenuController extends Controller
{
    public function __construct(protected MenuService $service)
    {
    }

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
    public function delete(int $id): ApiResponse
    {
        return $this->success($this->service->delete($id));
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
            'q' => Queryable::ns('id,name'),

            // 菜单编码
            'code' => Queryable::like(),

            // 前端路由
            'route' => Queryable::like(),
        ];
        $request->validate($rules);
        $paging = $request->query('paging') === '1';
        $data = $service->context('paging', $paging)->pagination(
            $paging ? $rules : null
        );

        return $this->success($data);
    }

    /**
     * 菜单下拉框选择器
     *
     * - `新增` / `编辑` 菜单时的上级菜单下拉选择器选项
     * - `新增` / `编辑` 角色时的权限下拉选择器选项
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
        $options = Menu::findAll()->values()->map(fn ($item) => [
            'label' => $item->name,
            'value' => $item->id,
            'type' => $item->type,
            'pid' => $item->pid,
        ]);

        return $this->success($options);
    }

    /**
     * 可用菜单编码列表
     *
     * `新增` / `编辑` 菜单时的菜单编码下拉选择器选项
     *
     * 默认情况下，仅返回尚未添加到菜单中的编码
     *
     * 可通过 `all=1` 返回所有可用的菜单编码
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
        return $this->handleUpdate($action, $id);
    }

    /**
     * 更新启用状态
     *
     * @param  int  $id  菜单id
     * @return ApiResponse<Updated>
     */
    public function updateEnabled(UpdateEnabledAction $action, int $id): ApiResponse
    {
        return $this->handleUpdate($action, $id);
    }

    /**
     * 更新显示状态
     *
     * @param  int  $id  菜单id
     * @return ApiResponse<Updated>
     */
    public function updateVisible(UpdateVisibleAction $action, int $id): ApiResponse
    {
        return $this->handleUpdate($action, $id);
    }

    /**
     * 更新操作
     */
    private function handleUpdate(MenuAction $action, int $id): ApiResponse
    {
        return $this->success($action->handle($id));
    }
}

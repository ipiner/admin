<?php

declare(strict_types=1);

namespace App\Modules\System\Role;

use App\Http\Controllers\Controller;
use App\Models\System\Role;
use App\Modules\System\Role\Actions\CreateRoleAction;
use App\Modules\System\Role\Actions\UpdateRoleAction;
use Dedoc\Scramble\Attributes\Group;
use Pin\Http\ApiResponse;
use Pin\Pagination\Pagination;
use Pin\Scramble\Created;
use Pin\Scramble\Deleted;
use Pin\Scramble\SelectOption;
use Pin\Scramble\Updated;

/**
 * 角色管理和角色选择器接口。
 */
#[Group('系统 / 角色')]
class RoleController extends Controller
{
    /**
     * 新增角色
     *
     * @return ApiResponse<Created>
     */
    public function create(CreateRoleAction $action): ApiResponse
    {
        return $this->success($action->handle());
    }

    /**
     * 删除角色
     *
     * @param  int  $id  角色id
     * @return ApiResponse<Deleted>
     */
    public function delete(RoleService $service, int $id): ApiResponse
    {
        return $this->success($service->delete($id));
    }

    /**
     * 角色列表
     *
     * @return ApiResponse<Pagination<RoleResource>>
     */
    public function index(): ApiResponse
    {
        $data = Role::orderBy('id')
            ->with('menus')
            ->pagination()
            ->toArray(RoleResource::class);

        return $this->success($data);
    }

    /**
     * 更新角色
     *
     * @param  int  $id  角色id
     * @return ApiResponse<Updated>
     */
    public function update(UpdateRoleAction $action, int $id): ApiResponse
    {
        return $this->success($action->handle($id));
    }

    /**
     * 角色下拉框选择器
     *
     * - `新增` / `编辑` 管理员时的角色下拉选择器选项
     *
     * @return ApiResponse<SelectOption[]>
     */
    public function selector(): ApiResponse
    {
        $options = Role::findAll()->map(fn (Role $item) => [
            'label' => $item->name,
            'value' => $item->id,
        ])
            ->values();

        return $this->success($options);
    }
}

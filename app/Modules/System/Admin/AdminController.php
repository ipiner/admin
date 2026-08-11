<?php

declare(strict_types=1);

namespace App\Modules\System\Admin;

use App\Http\Controllers\Controller;
use App\Models\System\Admin;
use App\Modules\System\Admin\Actions\CreateAdminAction;
use App\Modules\System\Admin\Actions\UpdateAdminAction;
use App\Modules\Upload\UploadService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Pagination\Pagination;
use Pin\Scramble\Created;
use Pin\Scramble\Deleted;
use Pin\Scramble\Updated;

/**
 * 管理员账号管理接口。
 */
#[Group('系统 / 管理员')]
class AdminController extends Controller
{
    /**
     * 新增管理员
     *
     * @return ApiResponse<Created>
     */
    public function create(CreateAdminAction $action): ApiResponse
    {
        return $this->success($action->handle());
    }

    /**
     * 删除管理员
     *
     * @param  int  $id  管理员id
     * @return ApiResponse<Deleted>
     */
    public function delete(AdminService $service, int $id): ApiResponse
    {
        return $this->success($service->delete($id));
    }

    /**
     * 管理员列表
     *
     * @return ApiResponse<Pagination<AdminResource>>
     */
    public function index(Request $request): ApiResponse
    {
        $rules = [
            /**
             * 关键字，支持查询 `id` / `用户名` / `姓名`
             *
             * @example 1 / admin / 系统管理员
             */
            'q' => 'nullable|string',
        ];
        $request->validate($rules);
        $data = Admin::orderBy('id')->queryable(['q' => 'ns:id|username|realname'])
            ->with('roles')
            ->pagination()
            ->toArray(AdminResource::class);

        return $this->success($data);
    }

    /**
     * 更新管理员
     *
     * @param  int  $id  管理员id
     * @return ApiResponse<Updated>
     */
    public function update(UpdateAdminAction $action, int $id): ApiResponse
    {
        return $this->success($action->handle($id));
    }

    /**
     * 更新管理员头像
     *
     * @param  int  $id  管理员id
     * @return ApiResponse<Updated>
     */
    public function updateAvatar(Request $request, AdminService $service, int $id): ApiResponse
    {
        if (! $request->files->get('file')) {
            return $this->success($service->update($id, ['avatar' => '']));
        }

        // 这里专给scramble解析body用，真正验证在Upload中
        $request->validate([
            // 头像文件
            'file' => 'file',
        ]);

        return $this->success($service->update(
            $id,
            ['avatar' => new UploadService()->upload($request)->url()]
        ));
    }
}

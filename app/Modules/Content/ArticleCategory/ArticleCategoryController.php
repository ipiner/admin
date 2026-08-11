<?php

declare(strict_types=1);

namespace App\Modules\Content\ArticleCategory;

use App\Http\Controllers\Controller;
use App\Modules\Content\ArticleCategory\Actions\CreateArticleCategoryAction;
use App\Modules\Content\ArticleCategory\Actions\UpdateArticleCategoryAction;
use App\Modules\Content\Models\ArticleCategory;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Pagination\Pagination;
use Pin\Scramble\Created;
use Pin\Scramble\Deleted;
use Pin\Scramble\Updated;
use Pin\Validation\QueryableRules as Queryable;

/**
 * 文章分类控制器
 */
#[Group('内容 / 文章分类')]
class ArticleCategoryController extends Controller
{
    public function __construct(protected ArticleCategoryService $service)
    {
    }

    /**
     * 新增分类
     *
     * @return ApiResponse<Created>
     */
    public function create(CreateArticleCategoryAction $action): ApiResponse
    {
        return $this->success($action->handle());
    }

    /**
     * 删除分类
     *
     * @param  int  $id  分类id
     * @return ApiResponse<Deleted>
     */
    public function delete(int $id): ApiResponse
    {
        return $this->success($this->service->delete($id));
    }

    /**
     * 分类列表
     *
     * @return ApiResponse<Pagination<ArticleCategory>>
     */
    public function index(Request $request, ArticleCategoryService $service): ApiResponse
    {
        $rules = [
            // 是否分页
            'paging' => 'nullable|in:0,1',

            /**
             * 关键字，支持查询 `id` / `分类名称`
             *
             * @example 1 / 用户
             */
            'q' => Queryable::ns('id,name'),
        ];
        $request->validate($rules);
        $paging = $request->query('paging') === '1';
        $data = $service->context('paging', $paging)->pagination(
            $paging ? $rules : null
        );

        return $this->success($data);
    }

    /**
     * 分类下拉框选择器
     *
     * - `新增` / `编辑` 分类时的上级分类下拉选择器选项
     * - `新增` / `编辑` 角色时的权限下拉选择器选项
     *
     * @return ApiResponse<array{
     *     label: string,
     *     value: int,
     *     pid: int,
     *   }[]>
     */
    public function selector(): ApiResponse
    {
        $options = ArticleCategory::findAll()->values()->map(fn ($item) => [
            'label' => $item->name,
            'value' => $item->id,
            'pid' => $item->pid,
        ]);

        return $this->success($options);
    }

    /**
     * 更新分类
     *
     * @param  int  $id  分类id
     * @return ApiResponse<Updated>
     */
    public function update(UpdateArticleCategoryAction $action, int $id): ApiResponse
    {
        return $this->success($action->handle($id));
    }
}

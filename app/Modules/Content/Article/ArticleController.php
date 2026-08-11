<?php

declare(strict_types=1);

namespace App\Modules\Content\Article;

use App\Http\Controllers\Controller;
use App\Modules\Content\Article\Actions\CreateArticleAction;
use App\Modules\Content\Article\Actions\UpdateArticleAction;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Pagination\Pagination;
use Pin\Scramble\Created;
use Pin\Scramble\Deleted;
use Pin\Scramble\Updated;
use Pin\Validation\QueryableRules as Queryable;

/**
 * 文章控制器
 */
#[Group('内容 / 文章')]
class ArticleController extends Controller
{
    public function __construct(protected ArticleService $service)
    {
    }

    /**
     * 新增文章
     *
     * @return ApiResponse<Created>
     */
    public function create(CreateArticleAction $action): ApiResponse
    {
        return $this->success($action->handle());
    }

    /**
     * 删除文章
     *
     * @param  int  $id  id
     * @return ApiResponse<Deleted>
     */
    public function delete(int $id): ApiResponse
    {
        return $this->success($this->service->delete($id));
    }

    /**
     * 文章列表
     *
     * @return ApiResponse<Pagination<ArticleResource>>
     */
    public function index(Request $request, ArticleService $service): ApiResponse
    {
        $rules = [
            /**
             * 关键字，支持查询 `id` / `标题` / `内容`
             *
             * @example 1 / 标题 / 内容
             */
            'q' => Queryable::ns('id,title,content'),

            // 分类id
            'category_id' => Queryable::eqNumeric(),
        ];
        $request->validate($rules);
        $data = $service->pagination($rules)->toArray(ArticleResource::class);

        return $this->success($data);
    }

    /**
     * 更新文章
     *
     * @param  int  $id  id
     * @return ApiResponse<Updated>
     */
    public function update(UpdateArticleAction $action, int $id): ApiResponse
    {
        return $this->success($action->handle($id));
    }
}

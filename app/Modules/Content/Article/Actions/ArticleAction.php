<?php

declare(strict_types=1);

namespace App\Modules\Content\Article\Actions;

use App\Modules\Content\Article\ArticleService;
use App\Modules\Content\Article\CategoryMustExistRule;
use App\Modules\Content\Models\Article;
use App\Modules\Content\Models\ArticleCategory;
use Illuminate\Support\Arr;
use Pin\Action\Action;
use Pin\Faker\Fake;
use Pin\Validation\Rules\Unique;

/**
 * 文章创建和更新动作共享的验证规则。
 */
class ArticleAction extends Action
{
    /**
     * 注入文章写入服务。
     */
    public function __construct(protected ArticleService $service)
    {
    }

    /**
     * 文章创建和更新共享的基础验证规则。
     */
    protected function basicRules(): array
    {
        return [
            // 标题
            'title' => [
                'required',
                'string',
                'unique' => new Unique(Article::class)->ignore(
                    (int) $this->context->get('id')
                ),
            ],

            // 内容
            'content' => 'required|string',

            /**
             * 分类
             */
            'category_id' => [
                'required',
                'integer',
                new CategoryMustExistRule(),
                Fake::make(fn () => Arr::random(
                    ArticleCategory::findAll()->keys()->toArray()
                )),
            ],
        ];
    }
}

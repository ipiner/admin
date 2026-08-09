<?php

declare(strict_types=1);

namespace App\Modules\Content\ArticleCategory\Actions;

use App\Modules\Content\ArticleCategory\ArticleCategoryService;
use Pin\Tree\Action;

/**
 * 分类创建和更新动作共享的验证规则。
 */
class ArticleCategoryAction extends Action
{
    /**
     * 注入分类树写入服务。
     */
    public function __construct(ArticleCategoryService $service)
    {
        parent::__construct($service);
    }

    /**
     * 分类创建和更新共享的基础验证规则。
     */
    protected function basicRules(?int $id = null, ?int $pid = null): array
    {
        $id ??= (int) $this->context->get('id');

        return [
            ...parent::basicRules($id, $pid),
        ];
    }
}

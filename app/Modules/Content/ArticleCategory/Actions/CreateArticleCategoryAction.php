<?php

declare(strict_types=1);

namespace App\Modules\Content\ArticleCategory\Actions;

use Pin\Services\Results\CreateResult;

/**
 * 创建分类节点。
 */
class CreateArticleCategoryAction extends ArticleCategoryAction
{
    /**
     * 执行分类创建流程。
     */
    public function handle(): CreateResult
    {
        return $this->service->create($this->validated());
    }

    /**
     * 创建分类请求验证规则。
     */
    protected function rules(): array
    {
        // ...展开，Scramble可识别
        return [
            ...$this->basicRules(),
        ];
    }
}

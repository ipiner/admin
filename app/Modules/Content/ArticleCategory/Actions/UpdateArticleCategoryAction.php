<?php

declare(strict_types=1);

namespace App\Modules\Content\ArticleCategory\Actions;

use Pin\Services\Results\UpdateResult;

/**
 * 更新分类主体字段。
 */
class UpdateArticleCategoryAction extends ArticleCategoryAction
{
    /**
     * 执行分类更新流程。
     */
    public function handle(int $id): UpdateResult
    {
        return $this->service->update($id, $this->validated());
    }

    /**
     * 更新分类主体字段的验证规则。
     */
    public function rules(): array
    {
        return [
            ...$this->basicRules(),
            // 数据版本号
            'v' => 'required|integer',
        ];
    }
}

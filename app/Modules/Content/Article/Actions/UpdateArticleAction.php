<?php

declare(strict_types=1);

namespace App\Modules\Content\Article\Actions;

use Pin\Services\Results\UpdateResult;

/**
 * 更新文章主体字段。
 */
class UpdateArticleAction extends ArticleAction
{
    /**
     * 执行文章更新流程。
     */
    public function handle(int $id): UpdateResult
    {
        return $this->service->update($id, $this->validated());
    }

    /**
     * 更新文章主体字段的验证规则。
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

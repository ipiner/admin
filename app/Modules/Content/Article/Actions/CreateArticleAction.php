<?php

declare(strict_types=1);

namespace App\Modules\Content\Article\Actions;

use Pin\Services\Results\CreateResult;

class CreateArticleAction extends ArticleAction
{
    /**
     * 执行文章创建流程。
     */
    public function handle(): CreateResult
    {
        return $this->service->create($this->validated());
    }

    /**
     * 创建文章请求验证规则。
     */
    protected function rules(): array
    {
        // ...展开，Scramble可识别
        return [
            ...$this->basicRules(),
        ];
    }
}

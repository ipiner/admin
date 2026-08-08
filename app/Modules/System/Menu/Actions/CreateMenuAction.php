<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

use Pin\Services\Results\CreateResult;

/**
 * 创建菜单节点。
 */
class CreateMenuAction extends MenuAction
{
    /**
     * 执行菜单创建流程。
     */
    public function handle(): CreateResult
    {
        return $this->service->create($this->validated());
    }

    /**
     * 创建菜单请求验证规则。
     */
    protected function rules(): array
    {
        // ...展开，Scramble可识别
        return [
            ...$this->basicRules(),
        ];
    }
}

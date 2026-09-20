<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

use Pin\Services\Results\CreateResult;

/**
 * 创建菜单。
 */
class CreateMenuAction extends MenuAction
{
    /**
     * 创建菜单。
     */
    public function handle(): CreateResult
    {
        return $this->service->create($this->validated());
    }

    /**
     * 创建验证规则。
     */
    public function rules(): array
    {
        // 展开规则供 Scramble 解析。
        return [...$this->basicRules()];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

use Pin\Services\Results\UpdateResult;

/**
 * 更新菜单。
 */
class UpdateMenuAction extends MenuAction
{
    /**
     * 更新菜单。
     */
    public function handle(int $id): UpdateResult
    {
        return $this->service->update($id, $this->validated());
    }

    /**
     * 更新验证规则。
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

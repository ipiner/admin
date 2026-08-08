<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

/**
 * 仅更新菜单启用状态。
 */
class UpdateEnabledAction extends UpdateMenuAction
{
    /**
     * 仅允许提交启用状态和版本号。
     */
    public function rules(): array
    {
        return [
            /**
             * 启用
             *
             * @example 1
             */
            'enabled' => $this->basicRules()['enabled'],

            // 数据版本号
            'v' => 'required|integer',
        ];
    }
}

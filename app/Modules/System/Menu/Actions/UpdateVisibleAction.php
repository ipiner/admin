<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

/**
 * 仅更新菜单显示状态。
 */
class UpdateVisibleAction extends UpdateMenuAction
{
    /**
     * 仅允许提交显示状态和版本号。
     */
    public function rules(): array
    {
        return [
            /**
             * 显示
             *
             * @example 1
             */
            'visible' => $this->basicRules()['visible'],

            // 数据版本号
            'v' => 'required|integer',
        ];
    }
}

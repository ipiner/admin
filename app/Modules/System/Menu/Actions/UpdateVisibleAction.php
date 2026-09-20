<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

use Override;

/**
 * 更新显示状态。
 */
class UpdateVisibleAction extends UpdateMenuAction
{
    /**
     * 状态验证规则。
     */
    #[Override]
    public function rules(): array
    {
        return [
            /**
             * 显示
             *
             * @example 1
             */
            'visible' => 'required|'.$this->visibleRules(),

            // 数据版本号
            'v' => 'required|integer',
        ];
    }
}

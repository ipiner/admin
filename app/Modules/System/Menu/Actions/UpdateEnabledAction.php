<?php

declare(strict_types=1);

namespace App\Modules\System\Menu\Actions;

use Override;

/**
 * 更新启用状态
 */
class UpdateEnabledAction extends UpdateMenuAction
{
    /**
     * 状态验证规则
     */
    #[Override]
    public function rules(): array
    {
        return [
            /**
             * 启用
             *
             * @example 1
             */
            'enabled' => 'required|'.$this->enabledRules(),

            // 数据版本号
            'v' => 'required|integer',
        ];
    }
}

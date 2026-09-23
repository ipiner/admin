<?php

declare(strict_types=1);

namespace App\Modules\System\Admin\Actions;

use Override;
use Pin\Services\Results\UpdateResult;

/**
 * 更新管理员启用状态
 */
class UpdateEnabledAction extends UpdateAdminAction
{
    /**
     * 更新管理员启用状态
     */
    public function handle(int $id): UpdateResult
    {
        return $this->service->update($id, $this->validated());
    }

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
            'enabled' => 'required|integer|in:0,1|',

            // 数据版本号
            'v' => 'required|integer',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models\System;

use Override;
use Pin\Modules\Log\Models\Concerns\HasOperationLog;

/**
 * 后台菜单
 *
 * @mixin IdeHelperMenu
 */
class Menu extends \Pin\Access\Models\Menu
{
    use HasOperationLog;

    /**
     * 系统启用状态
     */
    public const int SYSTEM_ENABLED = 2;

    /**
     * 清除按钮路由
     */
    #[Override]
    protected function onSaving(): void
    {
        parent::onSaving();

        if ($this->type === static::BUTTON) {
            $this->route = '';
        }
    }
}

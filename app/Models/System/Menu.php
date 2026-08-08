<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\IdeHelperMenu;
use Pin\Modules\Log\Models\Concerns\HasOperationLog;

/**
 * 后台菜单树模型。
 *
 * @mixin IdeHelperMenu
 */
class Menu extends \Pin\Access\Models\Menu
{
    use HasOperationLog;

    /**
     * 系统启用
     *
     * 该状态无法禁用/删除
     */
    public const int SYSTEM_ENABLED = 2;

    protected function onSaving(): void
    {
        parent::onSaving();
        if ($this->type === static::BUTTON) {
            $this->route = '';
        }
    }
}

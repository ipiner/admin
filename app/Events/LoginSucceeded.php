<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\System\Admin;

/**
 * 登录成功事件
 */
class LoginSucceeded
{
    public function __construct(public Admin $admin)
    {
    }
}

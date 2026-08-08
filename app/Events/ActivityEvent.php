<?php

declare(strict_types=1);

namespace App\Events;

use Pin\Modules\Log\Events\IActivityEvent;

/**
 * 行为日志事件枚举
 */
enum ActivityEvent: string implements IActivityEvent
{
    use \Pin\Modules\Log\Events\ActivityEvent;

    case LoginSucceeded = 'login.succeeded|登录系统|system';
    case LoginFailed = 'login.failed|登录失败|system';
    case Logout = 'login.logout|退出登录|system';
}

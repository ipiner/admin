<?php

declare(strict_types=1);

namespace App\Events;

use Pin\Modules\Log\Events\ActivityEvent as ActivityEventTrait;
use Pin\Modules\Log\Events\IActivityEvent;

/**
 * 行为日志事件。
 */
enum ActivityEvent: string implements IActivityEvent
{
    use ActivityEventTrait;

    case LoginSucceeded = 'login.succeeded|登录系统|system';
    case LoginFailed = 'login.failed|登录失败|system';
    case Logout = 'login.logout|退出登录|system';
}

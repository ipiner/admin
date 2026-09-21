<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ActivityEvent;
use App\Events\LoginSucceeded;
use App\Models\System\Admin;
use Pin\Modules\Log\Facades\Log;
use Pin\Modules\Log\Payloads\ActivityPayload;
use Pin\Modules\Log\Payloads\LoginPayload;

/**
 * 登录成功日志监听器
 */
class LogLoginSucceeded
{
    /**
     * 记录登录和行为日志
     */
    public function handle(LoginSucceeded $event): void
    {
        $this->createLoginLog($event->admin);
        $this->createActivityLog($event->admin);
    }

    /**
     * 记录登录成功
     */
    protected function createLoginLog(Admin $admin): void
    {
        Log::create(new LoginPayload($admin));
    }

    /**
     * 记录登录成功行为
     */
    protected function createActivityLog(Admin $admin): void
    {
        Log::create(
            new ActivityPayload(ActivityEvent::LoginSucceeded)
                ->subject(null, '系统')
                ->uid($admin->id)
                ->username($admin->username)
        );
    }
}

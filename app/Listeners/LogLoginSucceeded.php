<?php

namespace App\Listeners;

use App\Events\ActivityEvent;
use App\Events\LoginSucceeded;
use App\Models\System\Admin;
use Pin\Modules\Log\Facades\Log;
use Pin\Modules\Log\Payloads\ActivityPayload;
use Pin\Modules\Log\Payloads\LoginPayload;

class LogLoginSucceeded
{
    /**
     * Handle the event.
     */
    public function handle(LoginSucceeded $event): void
    {
        $this->createLoginLog($event->admin);
        $this->createActivityLog();
    }

    /**
     * 登录成功日志
     */
    protected function createLoginLog(Admin $admin): void
    {
        Log::create(new LoginPayload($admin));
    }

    /**
     * 登录成功行为日志
     */
    protected function createActivityLog(): void
    {
        $payload = new ActivityPayload(ActivityEvent::LoginSucceeded)->subject(null, '系统');
        Log::create($payload);
    }
}

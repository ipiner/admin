<?php

namespace App\Listeners;

use App\Events\ActivityEvent;
use App\Events\LoginFailed;
use Pin\Modules\Log\Facades\Log;
use Pin\Modules\Log\Payloads\ActivityPayload;
use Pin\Modules\Log\Payloads\LoginPayload;

class LogLoginFailed
{
    /**
     * Handle the event.
     */
    public function handle(LoginFailed $event): void
    {
        $this->createLoginLog($event);
        $this->createActivityLog($event);
    }

    /**
     * 登录失败日志
     */
    protected function createLoginLog(LoginFailed $event): void
    {
        $payload = new LoginPayload(
            $event->admin,
            $event->internalCode,
            $event->message
        )
            ->context($event->context);
        Log::create($payload);
    }

    /**
     * 登录失败行为日志
     */
    protected function createActivityLog(LoginFailed $event): void
    {
        $payload = new ActivityPayload(ActivityEvent::LoginFailed)
            ->subject(null, '系统')
            ->uid($event->admin->id)
            ->username($event->admin->username)
            ->context([
                'code' => $event->internalCode,
                'message' => $event->message,
                ...$event->context,
            ]);
        Log::create($payload);
    }
}

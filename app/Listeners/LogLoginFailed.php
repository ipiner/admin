<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ActivityEvent;
use App\Events\LoginFailed;
use Pin\Modules\Log\Facades\Log;
use Pin\Modules\Log\Payloads\ActivityPayload;
use Pin\Modules\Log\Payloads\LoginPayload;

/**
 * 登录失败日志监听器。
 */
class LogLoginFailed
{
    /**
     * 记录登录和行为日志。
     */
    public function handle(LoginFailed $event): void
    {
        $this->createLoginLog($event);
        $this->createActivityLog($event);
    }

    /**
     * 记录登录失败。
     */
    protected function createLoginLog(LoginFailed $event): void
    {
        Log::create(
            new LoginPayload($event->admin, $event->internalCode, $event->message)
                ->context($event->context)
        );
    }

    /**
     * 记录登录失败行为。
     */
    protected function createActivityLog(LoginFailed $event): void
    {
        Log::create(
            new ActivityPayload(ActivityEvent::LoginFailed)
                ->subject(null, '系统')
                ->uid($event->admin->id)
                ->username($event->admin->username)
                ->context([
                    ...$event->context,
                    'code' => $event->internalCode,
                    'message' => $event->message,
                ])
        );
    }
}

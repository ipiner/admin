<?php

declare(strict_types=1);

namespace App\Modules\Auth\Concerns;

use App\Errors\Errors;
use App\Events\ActivityEvent;
use Illuminate\Support\Facades\Request;
use Pin\Access\Facades\Access;
use Pin\Errors\IError;
use Pin\Modules\Log\Facades\Log;
use Pin\Modules\Log\Payloads\ActivityPayload;
use Pin\Modules\Log\Payloads\LoginPayload;

/**
 * 登录日志
 */
trait HasLogging
{
    /**
     * 登录失败
     *
     * @return array{code: int, message: string, status: int}
     */
    protected function loginFail(
        int|IError $code,
        ?string $message = null,
        array $context = []
    ): array {
        $err = $this->resolveError($code, $message);

        $payload = new LoginPayload(
            $this->admin,
            $err['internal_code'],
            $err['message']
        )
            ->context($context);

        Log::create($payload);

        $payload = new ActivityPayload(ActivityEvent::LoginFailed)
            ->subject(null, '系统')
            ->uid($this->admin->id)
            ->username($this->admin->username)
            ->context([
                'code' => $err['internal_code'],
                'message' => $err['message'],
                ...$context,
            ]);
        Log::create($payload);

        return [
            'code' => $err['code'],
            'message' => $err['message'],
            'status' => $err['status'],
        ];
    }

    /**
     * 登录成功
     */
    protected function loginSuccess(): void
    {
        auth()->setUser($this->admin);
        Access::flushAccess($this->admin);
        $this->admin->withoutOperationLogging(function () {
            $this->admin->login_num += 1;
            $this->admin->last_login_at = (string) now();
            $this->admin->last_login_ip = Request::ip();
            $this->admin->update();
        });

        Log::create(new LoginPayload($this->admin));

        $payload = new ActivityPayload(ActivityEvent::LoginSucceeded)
            ->subject(null, '系统');
        Log::create($payload);
    }

    /**
     * 解析错误
     *
     * @return array{code: int, message: string, status: int, internal_code: int}
     */
    protected function resolveError(int|IError $code, ?string $message = null): array
    {
        if ($code instanceof IError) {
            return [
                'code' => $code instanceof Errors
                    ? Errors::LoginFailed->code() // 对外返回统一失败码
                    : $code->code(),
                'message' => $code->message(),
                'status' => $code->statusCode(),
                'internal_code' => $code->code(), // 内部日志使用
            ];
        }

        return [
            'code' => $code,
            'message' => (string) $message,
            'status' => 422,
            'internal_code' => $code,
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Mail\Mail;
use Override;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

/**
 * 全局异常处理器
 */
class Handler extends \Pin\Exceptions\Handler
{
    /**
     * 记录异常并发送邮件
     */
    #[Override]
    protected function reportThrowable(Throwable $e): void
    {
        parent::reportThrowable($e);
        $this->reportedThrowable($e);
    }

    /**
     * 发送异常邮件
     */
    protected function mailThrowable(Throwable $e): Mail|false
    {
        return $this->shouldMail($e) ? Mail::sendByThrowable($e) : false;
    }

    /**
     * 处理异常通知
     */
    protected function reportedThrowable(Throwable $e): Mail|false
    {
        try {
            return $this->mailThrowable($e);
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * 是否发送异常邮件
     */
    protected function shouldMail(Throwable $e): bool
    {
        return ! $e instanceof TransportExceptionInterface;
    }
}

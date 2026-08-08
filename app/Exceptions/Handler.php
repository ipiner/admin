<?php

declare(strict_types=1);

namespace App\Exceptions;

use App\Mail\Mail;
use Override;
use Symfony\Component\Mailer\Exception\TransportException;
use Throwable;

/**
 * 全局异常处理器
 */
class Handler extends \Pin\Exceptions\Handler
{
    /**
     * 上报异常
     */
    #[Override]
    public function report(Throwable $e)
    {
        parent::report($e);
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
     * 上报异常后置操作
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
     * 是否需要发送异常邮件
     */
    protected function shouldMail(Throwable $e): bool
    {
        if ($this->shouldntReport($e) || $e instanceof TransportException) {
            return false;
        }

        return true;
    }
}

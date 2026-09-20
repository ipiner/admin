<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Support\Facades\Mail as Mailer;
use Illuminate\Support\Str;
use Pin\Exceptions\Exception;
use Pin\Log\ExtraProcessor;
use Throwable;

/**
 * 异常通知邮件。
 *
 * @phpstan-consistent-constructor
 */
class Mail extends Mailable
{
    /**
     * @param  array<string, mixed>  $log  日志内容
     */
    public function __construct(public array $log)
    {
    }

    /**
     * 构造异常邮件。
     *
     * @param  array<string, mixed>  $info  日志补充信息
     */
    public static function fromThrowable(Throwable $e, array $info = []): static
    {
        return new static([
            'datetime' => date('Y-m-d H:i:s'),
            'message' => $e->getMessage(),
            'context' => [
                'exception' => [
                    'code' => $e->getCode(),
                    'class' => $e::class,
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString(),
                    'context' => $e instanceof Exception ? $e->getContext() : [],
                ],
            ],
            'extra' => ExtraProcessor::getExtra(),
            ...$info,
        ]);
    }

    /**
     * 发送异常邮件。
     *
     * @param  array<string, mixed>  $info  日志补充信息
     */
    public static function sendByThrowable(
        Throwable $e,
        array $info = [],
        bool $queue = true
    ): self|false {
        if (app()->hasDebugModeEnabled()) {
            return false;
        }

        $mail = $queue ? MailQueue::fromThrowable($e, $info) : static::fromThrowable($e, $info);
        $mail->to(config('mail.from.address'))->subject(sprintf(
            '[%s] %s',
            config('app.env'),
            Str::limit($e->getMessage(), 30)
        ));

        if ($queue) {
            Mailer::queue($mail);
        } else {
            Mailer::sendNow($mail);
        }

        return $mail;
    }

    /**
     * 邮件正文。
     */
    public function content(): Content
    {
        return new Content(view: 'emails.email', with: ['subject' => $this->subject]);
    }
}

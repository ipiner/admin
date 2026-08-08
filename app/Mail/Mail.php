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
 * 将异常日志渲染为邮件内容并发送给系统收件人。
 */
class Mail extends Mailable
{
    public function __construct(public array $log)
    {
    }

    /**
     * 根据异常构造邮件日志上下文。
     */
    public static function fromThrowable(Throwable $e, array $info = []): static
    {
        return new static([
            'datetime' => date('Y-m-d H:i:s'),
            'message' => $e->getMessage(),
            'context' => [
                'exception' => [
                    'code' => $e->getCode(),
                    'class' => get_class($e),
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
     * 按运行环境决定是否立即发送或队列发送异常邮件。
     */
    public static function sendByThrowable(Throwable $e, array $info = [], bool $queue = true): static|false
    {
        if (app()->hasDebugModeEnabled()) {
            return false;
        }

        $mail = $queue ? MailQueue::fromThrowable($e, $info) : static::fromThrowable($e, $info);
        $mail->to(config('mail.from.address'))->subject(sprintf(
            '[%s] %s',
            config('app.env'),
            Str::limit($e->getMessage(), 30)
        ));

        Mailer::send($mail);

        return $mail;
    }

    /**
     * Build the message.
     */
    public function content(): Content
    {
        return new Content()
            ->with('subject', $this->subject)
            ->view('emails.email');
    }
}

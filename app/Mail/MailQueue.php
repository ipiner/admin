<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;

/**
 * 带限流和去重中间件的异常邮件队列任务。
 */
class MailQueue extends Mail implements ShouldQueue
{
    /**
     * 每个异常消息每分钟最多发送一次。
     */
    public const LIMIT_1_M = 'mail:1/m';

    /**
     * 队列去重锁 key。
     */
    public string $overlappingKey;

    /**
     * Laravel RateLimiter 名称。
     */
    public string $limiterName;

    /**
     * 限流分组 key。
     */
    public string $limitBy;

    /**
     * {@inheritdoc}
     */
    public function __construct(public array $log)
    {
        parent::__construct($log);
        $this->setOverlappingKey($messageId = md5($log['message']));
        $this->setLimit(static::LIMIT_1_M, $messageId);
    }

    /**
     * 返回队列限流使用的业务维度。
     */
    public function getLimitBy(): string
    {
        return $this->limitBy;
    }

    /**
     * 生成队列限流和去重中间件。
     */
    public function middleware(): array
    {
        $middlewares = [];

        if (isset($this->overlappingKey)) {
            $middlewares[] = (new WithoutOverlapping($this->overlappingKey))->dontRelease();
        }

        if (isset($this->limiterName)) {
            $middlewares[] = (new RateLimitedWithRedis($this->limiterName))->dontRelease();
        }

        return $middlewares;
    }

    /**
     * @return $this
     */
    public function setLimit(string $limiterName, string $limitBy = ''): static
    {
        $this->limiterName = $limiterName;
        $this->limitBy = $limitBy;

        return $this;
    }

    /**
     * @return $this
     */
    public function setOverlappingKey(string $overlappingKey): static
    {
        $this->overlappingKey = $overlappingKey;

        return $this;
    }
}

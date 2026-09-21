<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;

/**
 * 异常邮件队列
 */
class MailQueue extends Mail implements ShouldQueue
{
    /**
     * 每个异常消息每分钟最多发送一次
     */
    public const string LIMIT_1_M = 'mail:1/m';

    /**
     * 去重锁键
     */
    public string $overlappingKey;

    /**
     * 限流器名称
     */
    public string $limiterName;

    /**
     * 限流分组键
     */
    public string $limitBy;

    /**
     * @param  array<string, mixed>  $log  日志内容
     */
    public function __construct(array $log)
    {
        parent::__construct($log);

        $messageKey = md5($log['message']);
        $this->setOverlappingKey($messageKey);
        $this->setLimit(static::LIMIT_1_M, $messageKey);
    }

    /**
     * 获取限流分组键
     */
    public function getLimitBy(): string
    {
        return $this->limitBy;
    }

    /**
     * 队列去重与限流
     *
     * @return list<WithoutOverlapping|RateLimitedWithRedis>
     */
    public function middleware(): array
    {
        return [
            new WithoutOverlapping($this->overlappingKey)->dontRelease(),
            new RateLimitedWithRedis($this->limiterName)->dontRelease(),
        ];
    }

    /**
     * 设置限流规则
     */
    public function setLimit(string $limiterName, string $limitBy = ''): static
    {
        $this->limiterName = $limiterName;
        $this->limitBy = $limitBy;

        return $this;
    }

    /**
     * 设置去重锁键
     */
    public function setOverlappingKey(string $overlappingKey): static
    {
        $this->overlappingKey = $overlappingKey;

        return $this;
    }
}

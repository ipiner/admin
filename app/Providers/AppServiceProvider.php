<?php

declare(strict_types=1);

namespace App\Providers;

use App\Mail\MailQueue;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * 应用服务。
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * 注册邮件限流。
     */
    public function boot(): void
    {
        RateLimiter::for(
            MailQueue::LIMIT_1_M,
            static fn (SendQueuedMailable $job): Limit => Limit::perMinute(1)
                ->by($job->mailable->getLimitBy())
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use App\Mail\MailQueue;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Mail\SendQueuedMailable;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

/**
 * 应用服务提供者
 *
 * @codeCoverageIgnore
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for(MailQueue::LIMIT_1_M, function (SendQueuedMailable $mailable) {
            return Limit::perMinute(1)->by($mailable->mailable->getLimitBy());
        });
    }
}

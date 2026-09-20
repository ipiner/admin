<?php

declare(strict_types=1);

namespace App\Providers;

use App\Routes\Auth\LoginRoute;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

/**
 * 路由服务。
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * 注册参数规则和接口限流。
     */
    public function boot(): void
    {
        Route::pattern('id', '[1-9][0-9]*');
        RateLimiter::for('api', $this->apiLimit(...));
    }

    /**
     * 生成接口限流策略。
     */
    protected function apiLimit(Request $request): Limit
    {
        $isLogin = $request->isRequest(LoginRoute::Login->name());
        $maxAttempts = $isLogin
            ? config('app.rate_limit.login', 10)
            : config('app.rate_limit.default', 60);
        $key = auth()->hasUser() ? auth()->user()->id : $request->ip();

        return Limit::perMinute($maxAttempts)->by('limit:'.($isLogin ? 'login-' : '').$key);
    }
}

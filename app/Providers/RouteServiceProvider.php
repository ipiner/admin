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
 * 注册路由参数规则和接口限流策略。
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @codeCoverageIgnore
     */
    public function boot(): void
    {
        Route::pattern('id', '[1-9][0-9]*');
        RateLimiter::for('api', fn (Request $request) => $this->limit($request));
    }

    /**
     * 登录接口和普通接口使用不同的每分钟限流次数。
     */
    private function getMaxAttempts(bool $isLogin): int
    {
        return $isLogin
            ? config('app.rate_limit.login', 10)
            : config('app.rate_limit.default', 60);
    }

    /**
     * 判断当前请求是否为登录接口。
     */
    private function isLoginRoute(Request $request): bool
    {
        return $request->isRequest(LoginRoute::Login->name());
    }

    /**
     * 根据登录接口和普通接口生成不同的限流 key。
     */
    private function limit(Request $request): Limit
    {
        $isLogin = $this->isLoginRoute($request);
        $key = auth()->hasUser() ? auth()->user()->id : $request->ip();
        $key = 'limit:'.($isLogin ? 'login-' : '').$key;

        return Limit::perMinute($this->getMaxAttempts($isLogin))->by($key);
    }
}

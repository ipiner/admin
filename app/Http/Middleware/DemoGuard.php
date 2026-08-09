<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\Exception;
use App\Routes\AccountRoute;
use App\Routes\System\AdminRoute;
use Closure;
use Illuminate\Http\Request;

class DemoGuard
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $this->shouldRun($request)) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();
        if ($routeName && str_starts_with($routeName, 'system.menus')) {
            throw new Exception('演示环境禁止操作菜单', 403)
                ->withStatusCode(403);
        }
        $this->handleDelete($request);
        $this->handleUpdate($request);

        return $next($request);
    }

    protected function shouldRun(Request $request): bool
    {
        if (
            ! $request->server('RUNNING_IN_DEMO')
            || $request->user()?->isAdministrator()
            || $request->isReading()
        ) {
            return false;
        }

        return true;
    }

    protected function handleDelete(Request $request)
    {
        if (! $request->isMethod('DELETE')) {
            return;
        }

        if (
            $request->isRequest(AdminRoute::Delete->name())
            && $request->route('id') === '2'
        ) {
            throw new Exception('演示环境禁止删除该管理员', 403)
                ->withStatusCode(403);
        }

    }

    protected function handleUpdate(Request $request)
    {
        if (! $request->isMethod('PUT')) {
            return;
        }

        if (
            $request->isRequest(AdminRoute::Update->name())
            && $request->route('id') === '2'
        ) {
            throw new Exception('演示环境禁止更新该管理员', 403)
                ->withStatusCode(403);
        }

        if (
            $request->isRequest(AccountRoute::UpdatePassword->name())
                && $request->user()?->id === 2) {
            throw new Exception('演示环境该管理员禁止更新密码', 403)
                ->withStatusCode(403);
        }
    }
}

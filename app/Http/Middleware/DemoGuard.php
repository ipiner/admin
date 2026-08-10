<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Exceptions\Exception;
use App\Models\System\Admin;
use App\Models\System\Menu;
use App\Routes\AccountRoute;
use App\Routes\System\AdminRoute;
use App\Routes\System\MenuRoute;
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

        $this->handleCreate($request);
        $this->handleDelete($request);
        $this->handleUpdate($request);

        return $next($request);
    }

    /*
     * 处理新增
     */
    protected function handleCreate(Request $request): void
    {
        if ($request->isMethod('POST')) {
            $this->handleCreateMenu($request);
        }
    }

    /*
     * 处理新增菜单
     */
    protected function handleCreateMenu(Request $request): void
    {
        if (! $request->isRequest(MenuRoute::Create->name())) {
            return;
        }

        $type = $request->json('type');
        if ($type && $type !== Menu::BUTTON) {
            $this->throws('菜单只能添加按钮');
        }
    }

    /*
     * 删除处理
     */
    protected function handleDelete(Request $request): void
    {
        if ($request->isMethod('DELETE')) {
            $this->handleDeleteAdmin($request);
            $this->handleDeleteMenu($request);
        }
    }

    /**
     * 处理删除管理员
     */
    protected function handleDeleteAdmin(Request $request): void
    {
        if (
            $request->isRequest(AdminRoute::Delete->name())
            && $this->isProtectedAdminRequest($request)
        ) {
            $this->throws('禁止删除该管理员');
        }
    }

    /**
     * 处理删除菜单
     */
    protected function handleDeleteMenu(Request $request): void
    {
        if (
            $request->isRequest(MenuRoute::Delete->name())
            && ($menu = Menu::find((int) $request->route('id')))
            && $menu->type !== Menu::BUTTON
        ) {
            $this->throws('菜单只能删除按钮');
        }
    }

    /**
     * 处理更新
     */
    protected function handleUpdate(Request $request): void
    {
        if ($request->isMethod('PUT')) {
            $this->handleUpdateAdmin($request);
            $this->handleUpdateMenu($request);
        }
    }

    /**
     * 处理更新管理员
     */
    protected function handleUpdateAdmin(Request $request): void
    {
        if (
            $request->isRequest(AdminRoute::Update->name())
            && $this->isProtectedAdminRequest($request)
        ) {
            $this->throws('禁止更新该管理员');
        }

        if (
            $request->isRequest(AccountRoute::UpdatePassword->name())
            && $this->isProtectedAdmin($request->user())
        ) {
            $this->throws('该管理员禁止更新密码');
        }
    }

    /**
     * 处理更新菜单
     */
    protected function handleUpdateMenu(Request $request): void
    {
        if (
            $request->isRequest([MenuRoute::Update->name(), MenuRoute::UpdateVisible->name(), MenuRoute::UpdateEnabled->name()])
            && ($menu = Menu::find((int) $request->route('id')))
            && $menu->type !== Menu::BUTTON
        ) {
            $this->throws('菜单只能更新按钮');
        }
    }

    /**
     * 作用户是否为受保护管理员
     */
    protected function isProtectedAdmin(?Admin $admin): bool
    {
        return $admin?->username === 'test-admin';
    }

    /**
     * 被操作用户是否为受保护管理员
     */
    protected function isProtectedAdminRequest(Request $request): bool
    {
        $admin = Admin::find((int) $request->route('id'));

        return $this->isProtectedAdmin($admin);
    }

    /**
     * 是否执行验证
     */
    protected function shouldRun(Request $request): bool
    {
        return $request->server('RUNNING_IN_DEMO')
            && ! $request->user()?->isAdministrator()
            && ! $request->isReading();
    }

    /**
     * 抛异常
     */
    protected function throws(string $message): never
    {
        throw new Exception('演示环境'.$message, 403)
            ->withStatusCode(403);
    }
}

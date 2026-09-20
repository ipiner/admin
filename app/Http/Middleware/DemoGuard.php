<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Errors\Errors;
use App\Events\LoginFailed;
use App\Exceptions\Exception;
use App\Models\System\Admin;
use App\Models\System\Menu;
use App\Routes\AccountRoute;
use App\Routes\Auth\LoginRoute;
use App\Routes\System\AdminRoute;
use App\Routes\System\MenuRoute;
use Closure;
use Illuminate\Http\Request;

/**
 * 演示环境操作限制。
 */
class DemoGuard
{
    /**
     * 校验演示环境请求。
     */
    public function handle(Request $request, Closure $next): mixed
    {
        if (! $this->shouldRun($request)) {
            return $next($request);
        }

        match ($request->method()) {
            'POST' => $this->handleCreate($request),
            'DELETE' => $this->handleDelete($request),
            'PUT' => $this->handleUpdate($request),
            default => null,
        };

        return $next($request);
    }

    /**
     * 校验 admin 登录。
     */
    protected function handleAdminLogin(Request $request): void
    {
        if (! $request->isRequest(LoginRoute::Login->name())) {
            return;
        }

        $username = $request->json('username');
        if (
            ! is_string($username)
            || strcasecmp($username, 'admin') !== 0
            || $request->cookie('admin_login_token') === '1'
        ) {
            return;
        }

        $code = Errors::LoginDisabled->code();
        $message = Errors::LoginDisabled->message();
        event(new LoginFailed(
            Admin::find(Admin::ADMINISTRATOR_ID),
            $code,
            $message,
            $code,
        ));
        $this->throws($message, false);
    }

    /**
     * 校验新增请求。
     */
    protected function handleCreate(Request $request): void
    {
        $this->handleAdminLogin($request);
        $this->handleCreateMenu($request);
    }

    /**
     * 校验新增菜单。
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

    /**
     * 校验删除请求。
     */
    protected function handleDelete(Request $request): void
    {
        $this->handleDeleteAdmin($request);
        $this->handleDeleteMenu($request);
    }

    /**
     * 校验删除管理员。
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
     * 校验删除菜单。
     */
    protected function handleDeleteMenu(Request $request): void
    {
        if (
            $request->isRequest(MenuRoute::Delete->name())
            && $this->isProtectedMenuRequest($request)
        ) {
            $this->throws('菜单只能删除按钮');
        }
    }

    /**
     * 校验更新请求。
     */
    protected function handleUpdate(Request $request): void
    {
        $this->handleUpdateAdmin($request);
        $this->handleUpdateMenu($request);
    }

    /**
     * 校验更新管理员。
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
     * 校验更新菜单。
     */
    protected function handleUpdateMenu(Request $request): void
    {
        if (
            $request->isRequest([
                MenuRoute::Update->name(),
                MenuRoute::UpdateVisible->name(),
                MenuRoute::UpdateEnabled->name(),
            ])
            && $this->isProtectedMenuRequest($request)
        ) {
            $this->throws('菜单只能更新按钮');
        }
    }

    /**
     * 是否为受保护管理员。
     */
    protected function isProtectedAdmin(?Admin $admin): bool
    {
        return $admin?->username === 'test-admin';
    }

    /**
     * 是否操作受保护管理员。
     */
    protected function isProtectedAdminRequest(Request $request): bool
    {
        return $this->isProtectedAdmin(Admin::find((int) $request->route('id')));
    }

    /**
     * 是否操作受保护菜单。
     */
    protected function isProtectedMenuRequest(Request $request): bool
    {
        $menu = Menu::find((int) $request->route('id'));
        if (! $menu) {
            return false;
        }

        return $menu->type !== Menu::BUTTON
            || $request->isRequest(MenuRoute::Update->name())
                && $request->json('type') === Menu::MENU;
    }

    /**
     * 是否执行演示环境校验。
     */
    protected function shouldRun(Request $request): bool
    {
        return $request->server('RUNNING_IN_DEMO')
            && ! $request->isReading()
            && ! $request->user()?->isAdministrator();
    }

    /**
     * 拒绝操作。
     */
    protected function throws(string $message, bool $withPrefix = true): never
    {
        throw new Exception(($withPrefix ? '演示环境' : '').$message, 403)
            ->withStatusCode(403);
    }
}

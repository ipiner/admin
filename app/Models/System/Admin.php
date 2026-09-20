<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Collection as ModelCollection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Override;
use Pin\Access\Contracts\AccessUser;
use Pin\Models\Concerns\CacheAll;
use Pin\Models\Concerns\SoftDeletes;
use Pin\Support\Facades\Password;

/**
 * 管理员。
 *
 * @property-read ModelCollection<int, Role> $roles
 *
 * @mixin IdeHelperAdmin
 */
class Admin extends Model implements AccessUser, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
    use CacheAll, SoftDeletes;

    public const int ADMINISTRATOR_ID = 1;

    /**
     * @var list<string>
     */
    protected $hidden = ['password', 'salt', 'role_id'];

    /**
     * 获取可访问菜单。
     *
     * @return Collection<int, Menu>
     */
    #[Override]
    public function accessibleMenus(): Collection
    {
        if (! $this->isAdministrator()) {
            $this->load('roles');
        }

        if ($this->hasAllAccess()) {
            return Menu::findAll();
        }

        $menus = [];
        foreach ($this->roles->load('menus') as $role) {
            foreach ($role->menus as $menu) {
                $menus[$menu->id] = $menu;
            }
        }

        return collect($menus);
    }

    /**
     * 是否拥有全部权限。
     */
    #[Override]
    public function hasAllAccess(): bool
    {
        return $this->isAdministrator()
            || $this->roles->contains(static fn (Role $role) => $role->isSuperRole());
    }

    /**
     * 生成密码哈希。
     */
    public function hashPassword(?string $password = null): string
    {
        return Password::hash($password ?? $this->password, $this->salt);
    }

    /**
     * 是否为超级管理员。
     */
    public function isAdministrator(): bool
    {
        return $this->id === static::ADMINISTRATOR_ID;
    }

    /**
     * 关联角色。
     *
     * @return BelongsToMany<Role, $this>
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_admins', 'uid');
    }

    /**
     * 初始化密码。
     */
    #[Override]
    protected function onCreating(): void
    {
        parent::onCreating();

        $this->salt ??= Str::random(8);
        $this->password = $this->hashPassword($this->password);
    }

    /**
     * 隐藏日志中的密码。
     */
    #[Override]
    protected function transformOperationValue(string $key, mixed $value): mixed
    {
        return $key === 'password' ? '******' : $value;
    }
}

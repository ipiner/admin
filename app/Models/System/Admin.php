<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\IdeHelperAdmin;
use App\Models\Model;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Pin\Access\Contracts\AccessUser;
use Pin\Models\Concerns\CacheAll;
use Pin\Models\Concerns\SoftDeletes;
use Pin\Support\Facades\Password;

/**
 * 后台管理员账号模型。
 *
 * @mixin IdeHelperAdmin
 */
class Admin extends Model implements AccessUser, AuthorizableContract, CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword, MustVerifyEmail;
    use CacheAll, SoftDeletes;

    public const int ADMINISTRATOR_ID = 1;

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'salt', 'role_id'];

    /**
     * 汇总管理员角色关联的菜单权限。
     */
    public function accessibleMenus(): Collection
    {
        if ($this->hasAllAccess()) {
            return Menu::findAll();
        }

        $menus = [];
        foreach ($this->roles()->with('menus')->get() as $role) {
            /** @var Role $role */
            foreach ($role->menus as $menu) {
                $menus[$menu->id] = $menu;
            }
        }

        return collect($menus);
    }

    /**
     * 判断管理员是否直接或通过角色拥有超级管理员权限。
     */
    public function hasAllAccess(): bool
    {
        return $this->isAdministrator()
            || $this->roles->first(fn (Role $item) => $item->isSuperRole());
    }

    /**
     * 使用管理员 salt 生成密码哈希。
     */
    public function hashPassword(?string $password = null): string
    {
        return Password::hash($password ?: $this->password, $this->salt);
    }

    /**
     * 超级管理员？
     */
    public function isAdministrator(): bool
    {
        return $this->id === static::ADMINISTRATOR_ID;
    }

    /**
     * 管理员拥有的角色关系。
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_admins', 'uid');
    }

    /**
     * 创建管理员前生成 salt 并哈希初始密码。
     */
    protected function onCreating(): void
    {
        parent::onCreating();
        $this->salt = $this->salt ?? Str::random(8);
        $this->password = $this->hashPassword($this->password);
    }

    /**
     * 日志字段值处理
     */
    protected function transformOperationValue(string $key, mixed $value): mixed
    {
        return $key === 'password' ? '******' : $value;
    }
}

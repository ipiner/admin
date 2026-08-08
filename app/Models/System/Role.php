<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\IdeHelperRole;
use App\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Pin\Models\Concerns\CacheAll;
use Pin\Models\Concerns\SoftDeletes;

/**
 * @property Collection $admins
 *
 * @mixin IdeHelperRole
 */
class Role extends Model
{
    use CacheAll, SoftDeletes;

    public const int SUPER_ROLE_ID = 1;

    /**
     * 超级管理员？
     */
    public function isSuperRole(): bool
    {
        return $this->id === static::SUPER_ROLE_ID;
    }

    /**
     * 角色拥有的菜单
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'role_menus');
    }
}

<?php

declare(strict_types=1);

namespace App\Models\System;

use App\Models\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Pin\Models\Concerns\CacheAll;
use Pin\Models\Concerns\SoftDeletes;

/**
 * 管理员角色。
 *
 * @property-read Collection<int, Menu> $menus
 *
 * @mixin IdeHelperRole
 */
class Role extends Model
{
    use CacheAll, SoftDeletes;

    public const int SUPER_ROLE_ID = 1;

    /**
     * 是否为超级角色。
     */
    public function isSuperRole(): bool
    {
        return $this->id === static::SUPER_ROLE_ID;
    }

    /**
     * 关联菜单。
     *
     * @return BelongsToMany<Menu, $this>
     */
    public function menus(): BelongsToMany
    {
        return $this->belongsToMany(Menu::class, 'role_menus');
    }
}

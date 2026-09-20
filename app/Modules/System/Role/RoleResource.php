<?php

declare(strict_types=1);

namespace App\Modules\System\Role;

use App\Models\System\Menu;
use App\Models\System\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Override;

/**
 * 角色资料。
 *
 * @mixin Role
 */
class RoleResource extends JsonResource
{
    /**
     * @var Role
     */
    public $resource;

    /**
     * 转换响应数据。
     */
    #[Override]
    public function toArray(Request $request): array
    {
        return array_merge(
            parent::toArray($request),
            [
                'super' => $this->isSuperRole(),
                'menus' => $this->menuOptions(),
            ],
        );
    }

    /**
     * 菜单选项。
     *
     * @return Collection<int, array{id: int, name: string, paths: int[]}>
     */
    protected function menuOptions(): Collection
    {
        return $this->resource->menus->map(static fn (Menu $menu): array => [
            // 菜单id
            'id' => $menu->id,

            // 菜单名称
            'name' => $menu->name,

            // 菜单路径
            'paths' => $menu->paths(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\System\Role;

use App\Models\System\Menu;
use App\Models\System\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * 角色列表响应资源。
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
     * 为角色列表补充超级角色标识和已关联菜单路径。
     */
    public function toArray(Request $request): array
    {
        return array_merge(
            parent::toArray($request),
            [
                'super' => $this->isSuperRole(),
                'menus' => $this->menus(),
            ],
        );
    }

    /**
     * 将角色关联菜单整理为前端权限树可消费的结构。
     */
    private function menus(): Collection
    {
        return $this->resource->menus->map(fn (Menu $item) => [
            // 菜单id
            'id' => $item->id,

            // 菜单名称
            'name' => $item->name,

            /**
             * 菜单路径
             *
             * @var int[]
             */
            'paths' => $item->paths(),
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Account;

use App\Models\System\Admin;
use App\Models\System\Menu;
use App\Modules\System\Admin\AdminResource;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use JsonSerializable;

/**
 * 当前登录账号资料、权限编码和可见菜单的响应资源。
 */
class AccountResource extends JsonResource
{
    /**
     * @var array{account: Admin, access_codes: string[], menus: Menu[]|null}
     */
    public $resource;

    /**
     * 转换账号资料及权限信息。
     */
    public function toArray(Request $request): array
    {
        return [
            ...new AdminResource($this->resource['account'])->toArray($request),
            // 由菜单编码组成的权限
            'access_codes' => $this->resource['account']->hasAllAccess()
                ? []
                : $this->resource['access_codes'],
            /**
             * 拥有的菜单权限
             *
             * @var AccountMenu[]|null
             */
            'menus' => $this->resource['menus']
                ? array_map(fn (array $item) => new AccountMenu(new Menu($item)), $this->resource['menus'])
                : $this->resource['menus'],
        ];
    }
}

/**
 * 账号菜单资源
 */
class AccountMenu implements Arrayable, JsonSerializable
{
    public function __construct(protected Menu $menu)
    {
    }

    /**
     * 实现 `JsonSerializable` 接口
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 输出前端路由和权限树所需的菜单字段。
     */
    public function toArray(): array
    {
        return [
            'id' => $this->menu->id,
            'pid' => $this->menu->pid,
            'name' => $this->menu->name,
            'code' => $this->menu->code,
            'enabled' => $this->menu->enabled,
            'visible' => $this->menu->visible,
            'icon' => $this->menu->icon,
            'path' => $this->menu->path,
            /**
             * @var int[]
             *
             * @example [3, 17, 32]
             */
            'paths' => $this->menu->paths,
            'route' => $this->menu->route,
        ];
    }
}

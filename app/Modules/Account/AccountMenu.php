<?php

declare(strict_types=1);

namespace App\Modules\Account;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;
use Override;

/**
 * 账号菜单
 *
 * @implements Arrayable<string, mixed>
 */
class AccountMenu implements Arrayable, JsonSerializable
{
    /**
     * @param  array<string, mixed>  $menu
     */
    public function __construct(protected array $menu)
    {
    }

    /**
     * 序列化菜单
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * 获取菜单字段
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(): array
    {
        return [
            /** @var int */
            'id' => $this->menu['id'],
            /** @var int */
            'pid' => $this->menu['pid'],
            'name' => $this->menu['name'],
            'code' => $this->menu['code'],
            /** @var int */
            'enabled' => $this->menu['enabled'],
            /** @var int */
            'visible' => $this->menu['visible'],
            'icon' => $this->menu['icon'],
            'path' => $this->menu['path'],
            /**
             * @var int[]
             *
             * @example [3, 17, 32]
             */
            'paths' => $this->menu['paths'],
            'route' => $this->menu['route'],
        ];
    }
}

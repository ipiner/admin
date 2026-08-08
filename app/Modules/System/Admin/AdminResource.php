<?php

declare(strict_types=1);

namespace App\Modules\System\Admin;

use App\Models\System\Admin;
use App\Models\System\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * 管理员列表响应资源。
 *
 * @mixin Admin
 */
class AdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return array_merge(
            parent::toArray($request),
            [
                /**
                 * @var array{id: int, name: string}[]
                 */
                'roles' => $this->getRoles($this->resource->roles),
                'super' => $this->isAdministrator(),
            ]
        );
    }

    /**
     * 归一化角色字段；超级管理员始终展示内置超级角色。
     */
    private function getRoles(Collection $roles): array
    {
        if ($this->hasAllAccess()) {
            return [
                [
                    /** 角色id */
                    'id' => 1,
                    'name' => Role::find(1)->name,
                ],
            ];
        }

        return $roles->map(fn ($item) => $item->only(['id', 'name']))->toArray();
    }
}

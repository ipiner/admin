<?php

declare(strict_types=1);

namespace App\Modules\System\Admin;

use App\Models\System\Admin;
use App\Models\System\Role;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * 管理员资料。
 *
 * @mixin Admin
 */
class AdminResource extends JsonResource
{
    /**
     * 转换响应数据。
     *
     * @return array<string, mixed>
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $hasAllAccess = $this->hasAllAccess();

        return array_merge(
            parent::toArray($request),
            [
                /**
                 * @var array{id: int, name: string}[]
                 */
                'roles' => $this->roleOptions($hasAllAccess),
                'super' => $this->isAdministrator(),
                'has_all_access' => $hasAllAccess,
            ]
        );
    }

    /**
     * 角色选项。
     *
     * @return array{id: int, name: string}[]
     */
    protected function roleOptions(bool $hasAllAccess): array
    {
        if ($hasAllAccess) {
            return [
                [
                    /** 角色id */
                    'id' => Role::SUPER_ROLE_ID,
                    'name' => Role::find(Role::SUPER_ROLE_ID)->name,
                ],
            ];
        }

        return $this->resource->roles
            ->map(static fn (Role $role) => $role->only(['id', 'name']))
            ->all();
    }
}

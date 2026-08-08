<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Models\System\Admin;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Pin\Auth\Auth;

/**
 * 登录成功响应资源。
 *
 * @mixin Admin
 */
class LoginResource extends JsonResource
{
    /**
     * 将管理员模型转换为登录响应。
     */
    public function toArray(Request $request): array
    {
        return static::forAdmin($this->resource);
    }

    /**
     * @return array{
     *   token: string,
     *   user: array{
     *   id: int,
     *   realname: string
     * }
     */
    public static function forAdmin(Admin $admin): array
    {
        return [
            'token' => Auth::token()->encode([
                'uid' => $admin->id,
                'jti' => sprintf('auth-token:%d-%s', $admin->id, uniqid()),
            ]),
            'user' => ['id' => $admin->id, 'realname' => $admin->realname],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Account;

use App\Models\System\Admin;
use App\Modules\System\Admin\AdminResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Override;

/**
 * 账号资料与权限。
 */
class AccountResource extends JsonResource
{
    /**
     * @var array{
     *     account: Admin,
     *     access_codes: list<string>,
     *     menus: array<int, array<string, mixed>>|null
     * }
     */
    public $resource;

    /**
     * 转换响应数据。
     */
    #[Override]
    public function toArray(Request $request): array
    {
        $account = new AdminResource($this->resource['account'])->toArray($request);

        return [
            ...$account,
            // 权限编码
            'access_codes' => $account['has_all_access'] ? [] : $this->resource['access_codes'],
            /**
             * 可访问菜单
             *
             * @var AccountMenu[]|null
             */
            'menus' => $this->resource['menus']
                ? array_map(
                    static fn (array $menu) => new AccountMenu($menu),
                    $this->resource['menus']
                )
                : $this->resource['menus'],
        ];
    }
}

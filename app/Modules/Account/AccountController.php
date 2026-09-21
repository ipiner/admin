<?php

declare(strict_types=1);

namespace App\Modules\Account;

use App\Http\Controllers\Controller;
use App\Models\System\Admin;
use App\Modules\Upload\UploadService;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Pin\Access\Facades\Access;
use Pin\Errors\Errors;
use Pin\Http\ApiResponse;
use Pin\Scramble\Updated;
use Pin\Services\Results\UpdateResult;
use Pin\Support\Facades\Password;

/**
 * 账号管理
 */
#[Group('用户中心')]
class AccountController extends Controller
{
    /**
     * 获取账号信息
     *
     * @param  Admin  $account
     * @return ApiResponse<AccountResource>
     */
    public function profile(Request $request, Authenticatable $account): ApiResponse
    {
        $request->validate([
            // 返回菜单
            'menus' => 'nullable|in:0,1',
        ]);
        $access = Access::forUser($account);

        return $this->success(new AccountResource([
            'account' => $account,
            'access_codes' => $access->codes(),
            'menus' => $request->integer('menus') ? $access->menus() : null,
        ]));
    }

    /**
     * 修改密码
     *
     * @param  Admin  $account
     * @return ApiResponse<Updated>
     */
    public function updatePassword(Request $request, Authenticatable $account): ApiResponse
    {
        $data = $request->validate(
            [
                /**
                 * 当前密码（加密传输）
                 *
                 * @example plain:123456
                 */
                'current_password' => 'required|string',

                /**
                 * 新密码（加密传输）
                 *
                 * @example plain:123456
                 */
                'password' => 'required|string',
            ],
            [],
            ['password' => '新密码']
        );
        if (! Password::check($data['current_password'], $account->salt, $account->password)) {
            Errors::UpdateFailed->throw('当前密码错误');
        }
        $updated = $account->update([
            'password' => $account->hashPassword($data['password']),
        ]);

        return $this->success(new UpdateResult($account, (bool) $updated));
    }

    /**
     * 更新头像
     *
     * @param  Admin  $account
     * @return ApiResponse<array{updated: bool, url: string|null}>
     */
    public function updateAvatar(
        Request $request,
        Authenticatable $account,
        UploadService $service
    ): ApiResponse {
        if (! $request->hasFile('file')) {
            return $this->success(
                [
                    'updated' => $account->update(['avatar' => '']),
                    'url' => null,
                ],
                '更新成功'
            );
        }

        // 这里专给scramble解析body用，真正验证在Upload中
        $request->validate([
            // 头像文件
            'file' => 'file',
        ]);

        $url = new UploadService()->upload($request)->url();

        return $this->success(
            [
                'updated' => $account->update(['avatar' => $url]),
                'url' => $url,
            ],
            '更新成功'
        );
    }

    /**
     * 更新个人信息
     *
     * @param  Admin  $account
     * @return ApiResponse<Updated>
     */
    public function updateProfile(Request $request, Authenticatable $account): ApiResponse
    {
        $data = $request->validate([
            'realname' => 'required|string',
        ]);

        $updated = $account->update($data);

        return $this->success(new UpdateResult($account, (bool) $updated));
    }
}

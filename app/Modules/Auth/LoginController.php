<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Events\ActivityEvent;
use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Pin\Http\ApiResponse;
use Pin\Modules\Log\Facades\Log;
use Pin\Modules\Log\Payloads\ActivityPayload;

/**
 * 后台登录和退出接口。
 */
#[Group('登录')]
class LoginController extends Controller
{
    /**
     * 登录
     *
     * @return ApiResponse<LoginResource>
     */
    public function login(LoginAction $action): ApiResponse
    {
        $user = $action->handle();
        if (is_array($user)) {
            return $this->error($user['code'], $user['message'])->withStatusCode($user['status']);
        }

        return $this->success(new LoginResource($user), '登录成功');
    }

    /**
     * 退出
     *
     * @return ApiResponse<null>
     */
    public function logout(): ApiResponse
    {
        if (auth()->user()) {
            $payload = new ActivityPayload(ActivityEvent::Logout)
                ->subject(null, '系统');
            Log::create($payload);

            auth()->logout();
        }

        return $this->success(null, '退出成功');
    }
}

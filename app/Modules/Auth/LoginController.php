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
 * 登录与注销
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
        $result = $action->handle();
        if (is_array($result)) {
            return $this->error($result['code'], $result['message'])
                ->withStatusCode($result['status']);
        }

        return $this->success(new LoginResource($result), '登录成功');
    }

    /**
     * 注销请求 Token
     *
     * @return ApiResponse<null>
     */
    public function logout(): ApiResponse
    {
        if (auth()->user()) {
            Log::create(new ActivityPayload(ActivityEvent::Logout)->subject(null, '系统'));

            auth()->logout();
        }

        return $this->success(null, '退出成功');
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Password\PasswordRule;

#[Group('验证')]
class ValidationController extends Controller
{
    /**
     * 验证密码
     *
     * - 长度8-32位
     * - 不能包含空格
     * - 至少需要包含字母、数字、特殊字符中的2种
     * - 不能包含连续5位以上的顺序字母或数字
     * - 不能包含连续重复5位以上的字符
     *
     * @return ApiResponse<null>
     */
    public function password(Request $request): ApiResponse
    {
        $this->validate($request, [
            /**
             * 密码（加密传输）
             *
             * 非生产环境下可以使用 `plain:123456` 格式的明文密码
             *
             * @example plain:123456
             */
            'password' => ['required', new PasswordRule()->requiredCharacterTypes(2)],
        ]);

        return $this->success();
    }
}

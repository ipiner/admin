<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Password\PasswordRule;

/**
 * 数据验证接口。
 */
#[Group('验证')]
class ValidationController extends Controller
{
    /**
     * 验证密码。
     *
     * @return ApiResponse<null>
     */
    public function password(Request $request): ApiResponse
    {
        $this->validate($request, [
            /**
             * 密码（加密传输）
             *
             * @example plain:test@123
             */
            'password' => [
                'required',
                'string',
                new PasswordRule()->requiredCharacterTypes(2),
            ],
        ]);

        return $this->success();
    }
}

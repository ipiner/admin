<?php

declare(strict_types=1);

namespace App\Modules\Auth\Concerns;

use Illuminate\Validation\Validator;
use Pin\Exceptions\ValidationException;
use Pin\Faker\Fake;

/**
 * 登录 Payload 校验。
 */
trait HasValidation
{
    /**
     * 验证失败时也写入登录日志，便于审计异常登录请求。
     */
    protected function failedValidation(Validator $validator): void
    {
        $username = $this->payload('username') ?? 'empty:'.uniqid();
        $this->admin = $this->findAdmin($username);
        [$code, $message] = ValidationException::resolveCodeMessage($validator->errors()->first());
        $this->loginFail(
            $code,
            $message,
            $validator->errors()->toArray(),
        );
        parent::failedValidation($validator);
    }

    /**
     * 登录请求验证规则。
     */
    protected function rules(): array
    {
        return [
            // 用户名
            'username' => 'required|string',

            /**
             * 密码（加密传输）
             *
             * @example plain:123456
             */
            'password' => 'required|fake:password',
            /**
             * 验证码，格式 `input.token`
             *
             * @example plain:a|a
             */
            'captcha' => [
                'required',
                Fake::make(fn () => 'plain:a|a'),
            ],
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Errors\Errors;
use App\Events\LoginFailed;
use App\Events\LoginSucceeded;
use App\Models\System\Admin;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Validator;
use Pin\Access\Facades\Access;
use Pin\Action\Action;
use Pin\Captcha\Captcha;
use Pin\Errors\IError;
use Pin\Exceptions\ValidationException;
use Pin\Faker\Fake;
use Pin\Support\Facades\Password;

/**
 * 登录 Action
 */
class LoginAction extends Action
{
    protected Admin $admin;

    /**
     * @return Admin|array{code: int, message: string, status: int}
     */
    public function handle(): Admin|array
    {
        $data = $this->validated();
        $admin = $this->findAdmin($data['username']);

        // 验证码
        $res = Captcha::verify($data['captcha'], $admin->captcha_rule);
        if ($res->err !== null) {
            return $this->loginFail(
                $admin,
                $res->err,
                null,
                Arr::except($res->toArray(), 'err'),
            );
        }

        // 用户名
        if ($admin->id === 0) {
            return $this->loginFail(
                $admin,
                Errors::LoginAccountNotFound,
                null,
                ['username' => $data['username']],
            );
        }

        // 密码
        if (! Password::check($data['password'], $admin->salt, $admin->password)) {
            return $this->loginFail($admin, Errors::LoginPasswordMismatch);
        }

        $admin->transaction(fn () => $this->loginSuccess($admin));

        return $admin;
    }

    /**
     * 验证失败时也写入登录日志，便于审计异常登录请求。
     */
    protected function failedValidation(Validator $validator): void
    {
        $username = $this->payload('username') ?? 'empty:'.uniqid();
        [$code, $message] = ValidationException::resolveCodeMessage($validator->errors()->first());
        $this->loginFail(
            $this->findAdmin($username),
            $code,
            $message,
            $validator->errors()->toArray(),
        );
        parent::failedValidation($validator);
    }

    /**
     * 查询管理员
     */
    protected function findAdmin(string $username): Admin
    {
        return Admin::findBy('username', $username)
            ?? new Admin(['id' => 0, 'username' => $username]);
    }

    /**
     * 登录失败
     *
     * @return array{code: int, message: string, status: int}
     */
    protected function loginFail(
        Admin $admin,
        int|IError $code,
        ?string $message = null,
        array $context = []
    ): array {
        $err = $this->resolveError($code, $message);

        event(new LoginFailed(
            $admin,
            $err['code'],
            $err['message'],
            $err['internal_code'],
            $context,
        ));

        return [
            'code' => $err['code'],
            'message' => $err['message'],
            'status' => $err['status'],
        ];
    }

    /**
     * 登录成功
     */
    protected function loginSuccess(Admin $admin): void
    {
        auth()->setUser($admin);
        Access::flushAccess($admin);
        $admin->withoutOperationLogging(function () use ($admin) {
            $admin->login_num += 1;
            $admin->last_login_at = (string) now();
            $admin->last_login_ip = Request::ip();
            $admin->update();
        });

        event(new LoginSucceeded($admin));
    }

    /**
     * 解析错误
     *
     * @return array{code: int, message: string, status: int, internal_code: int}
     */
    protected function resolveError(int|IError $code, ?string $message = null): array
    {
        if ($code instanceof IError) {
            return [
                'code' => $code instanceof Errors
                    ? Errors::LoginFailed->code() // 对外返回统一失败码
                    : $code->code(),
                'message' => $code->message(),
                'status' => $code->statusCode(),
                'internal_code' => $code->code(), // 内部日志使用
            ];
        }

        return [
            'code' => $code,
            'message' => (string) $message,
            'status' => 422,
            'internal_code' => $code,
        ];
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

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
use Override;
use Pin\Action\Action;
use Pin\Captcha\Captcha;
use Pin\Errors\IError;
use Pin\Exceptions\ValidationException;
use Pin\Faker\Fake;
use Pin\Support\Facades\Password;
use Throwable;

/**
 * 管理员登录
 */
class LoginAction extends Action
{
    /**
     * 校验登录凭据
     *
     * @return Admin|array{code: int, message: string, status: int}
     */
    public function handle(): Admin|array
    {
        $data = $this->validated();
        $admin = $this->findAdmin($data['username']);

        $result = Captcha::verify($data['captcha'], $admin->captcha_rule);
        if ($result->err) {
            return $this->failLogin(
                $admin,
                $result->err,
                null,
                Arr::except($result->toArray(), 'err'),
            );
        }

        if ($admin->id === 0) {
            return $this->failLogin(
                $admin,
                Errors::LoginAccountNotFound,
                null,
                ['username' => $data['username']],
            );
        }

        if ($admin->isDisabled()) {
            return $this->failLogin(
                $admin,
                Errors::LoginAccountDisabled->code(),
                Errors::LoginAccountDisabled->message(),
            );
        }

        if (! Password::check($data['password'], $admin->salt, $admin->password)) {
            return $this->failLogin($admin, Errors::LoginPasswordMismatch);
        }

        $this->completeLogin($admin);

        return $admin;
    }

    /**
     * 记录参数验证失败
     */
    #[Override]
    protected function failedValidation(Validator $validator): void
    {
        $username = $this->payload('username');
        [$code, $message] = ValidationException::resolveCodeMessage($validator->errors()->first());
        $this->failLogin(
            $this->findAdmin(is_string($username) ? $username : ''),
            $code,
            $message,
            $validator->errors()->toArray(),
        );
        parent::failedValidation($validator);
    }

    /**
     * 查找登录账号
     */
    protected function findAdmin(string $username): Admin
    {
        return ($username !== '' ? Admin::findBy('username', $username) : null)
            ?? new Admin(['id' => 0, 'username' => $username]);
    }

    /**
     * 记录登录失败
     *
     * @return array{code: int, message: string, status: int}
     */
    protected function failLogin(
        Admin $admin,
        int|IError $code,
        ?string $message = null,
        array $context = []
    ): array {
        $error = $this->resolveError($code, $message);

        event(new LoginFailed(
            $admin,
            $error['code'],
            $error['message'],
            $error['internal_code'],
            $context,
        ));

        return [
            'code' => $error['code'],
            'message' => $error['message'],
            'status' => $error['status'],
        ];
    }

    /**
     * 完成登录
     */
    protected function completeLogin(Admin $admin): void
    {
        $guard = auth()->guard();
        $previousUser = $guard->hasUser() ? $guard->user() : null;

        try {
            $admin->getConnection()->transaction(function () use ($admin, $guard) {
                $guard->setUser($admin);
                $accessProvider = config('pin.access.access_provider');
                $accessProvider::flushAccess($admin);

                $admin->withoutOperationLogging(fn () => $admin->update([
                    'login_num' => $admin->login_num + 1,
                    'last_login_at' => (string) now(),
                    'last_login_ip' => Request::ip(),
                ]));

                event(new LoginSucceeded($admin));
            });
        } catch (Throwable $e) {
            $previousUser ? $guard->setUser($previousUser) : $guard->forgetUser();

            throw $e;
        }
    }

    /**
     * 解析登录错误
     *
     * @return array{code: int, message: string, status: int, internal_code: int}
     */
    protected function resolveError(int|IError $code, ?string $message = null): array
    {
        if ($code instanceof IError) {
            return [
                'code' => $code instanceof Errors
                    ? Errors::LoginFailed->code()
                    : $code->code(),
                'message' => $code->message(),
                'status' => $code->statusCode(),
                'internal_code' => $code->code(),
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
     * 登录请求验证规则
     */
    public function rules(): array
    {
        return [
            // 用户名
            'username' => 'required|string',

            /**
             * 密码（加密传输）
             *
             * @example plain:123456
             */
            'password' => 'required|string|fake:password',
            /**
             * 验证码，格式 `input|token`
             *
             * @example plain:a|a
             */
            'captcha' => [
                'required',
                'string',
                Fake::make(static fn () => 'plain:a|a'),
            ],
        ];
    }
}

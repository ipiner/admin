<?php

declare(strict_types=1);

namespace App\Modules\Auth;

use App\Errors\Errors;
use App\Models\System\Admin;
use Illuminate\Support\Arr;
use Pin\Actions\Action;
use Pin\Captcha\Captcha;
use Pin\Support\Facades\Password;

/**
 * 登录 Action
 */
class LoginAction extends Action
{
    protected Admin $admin;

    use Concerns\HasLogging,
        Concerns\HasValidation;

    /**
     * @return Admin|array{code: int, message: string, status: int}
     */
    public function handle(): Admin|array
    {
        $data = $this->validated();
        $this->admin = $this->findAdmin($data['username']);

        // 验证码
        $res = Captcha::verify($data['captcha'], $this->admin->captcha_rule);
        if ($res->err !== null) {
            return $this->loginFail(
                $res->err,
                null,
                Arr::except($res->toArray(), 'err'),
            );
        }

        // 用户名
        if ($this->admin->id === 0) {
            return $this->loginFail(
                Errors::LoginAccountNotFound,
                null,
                ['username' => $data['username']],
            );
        }

        // 密码
        if (! Password::check($data['password'], $this->admin->salt, $this->admin->password)) {
            return $this->loginFail(Errors::LoginPasswordMismatch);
        }

        $this->admin->transaction(fn () => $this->loginSuccess());

        return $this->admin;
    }

    /**
     * 查询管理员
     */
    protected function findAdmin(string $username): Admin
    {
        return Admin::findBy('username', $username)
            ?? new Admin(['id' => 0, 'username' => $username]);
    }
}

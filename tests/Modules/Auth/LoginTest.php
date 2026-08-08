<?php

declare(strict_types=1);

use App\Modules\Auth\LoginAction;
use App\Routes\Auth\LoginRoute;
use Database\Factories\System\AdminFactory;
use Pin\Auth\Auth;
use Pin\Captcha\Errors;
use Pin\Support\Facades\Password;

describe('login', function () {
    it('fails to login with required fields', function () {
        LoginRoute::Login->testJson($this)
            ->assertCode(422, 422)
            ->assertInvalid(['username', 'password', 'captcha']);
    });

    it('fails to login with captcha mismatch', function () {
        LoginRoute::Login->testJson(
            $this,
            LoginAction::fake([
                'username' => AdminFactory::testingAdmin()->username,
                'captcha' => 'plain:a|b',
            ])
        )
            ->assertCode(Errors::CaptchaMismatch);
    });

    it('fails to login with account not found', function () {
        LoginRoute::Login->testJson(
            $this,
            LoginAction::fake(['username' => uniqid()])
        )
            ->assertCode(App\Errors\Errors::LoginFailed);
    });

    it('fails to login with password mismatch', function () {
        LoginRoute::Login->testJson(
            $this,
            LoginAction::fake([
                'password' => Password::encodeToRequest('45678'),
                'username' => AdminFactory::testingAdmin()->username,
            ])
        )
            ->assertCode(App\Errors\Errors::LoginFailed);
    });

    it('logins successfully', function () {
        $admin = AdminFactory::new()->create();
        $token = LoginRoute::Login->testJson(
            $this,
            LoginAction::fake(['username' => $admin->username])
        )
            ->assertSuccessful()
            ->assertJsonPath('data.user.id', $admin->id)
            ->json('data.token');
        expect(Auth::token()->decode($token)->uid)->toBe($admin->id);
    });
});

it('logout successfully', function () {
    LoginRoute::Logout->testJson($this)->assertSuccessful();
});

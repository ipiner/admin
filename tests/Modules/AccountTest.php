<?php

declare(strict_types=1);

use App\Models\System\Admin;
use App\Routes\AccountRoute;
use Database\Factories\System\AdminFactory;
use Illuminate\Http\UploadedFile;
use Pin\Errors\Errors;
use Pin\Support\Facades\Password;

describe('profile', function () {
    it('fetches account profile without menus', function () {
        $this->withAuth(Admin::ADMINISTRATOR_ID);
        $data = AccountRoute::Profile->testJson($this)->json('data');

        expect($data['menus'])->toBeNull();
    });

    it('fetches account profile with menus', function () {
        $this->withAuth(Admin::ADMINISTRATOR_ID);
        $data = AccountRoute::Profile->testJson($this, ['menus' => 1])->json('data');

        expect($data['menus'])->not()->toBeNull();
    });
});

describe('update password', function () {
    it(updates('password'), function () {
        $admin = AdminFactory::new()->create(['password' => Password::encode('123456')]);
        $this->withAuth($admin);
        AccountRoute::UpdatePassword->testJson(
            $this,
            [
                'password' => Password::encodeToRequest('45678'),
                'current_password' => Password::encodeToRequest('123456'),
            ]
        )->assertUpdated();
    });
    it('fails to update password with wrong current password', function () {
        $admin = AdminFactory::testingAdmin();
        $this->withAuth($admin);
        AccountRoute::UpdatePassword->testJson(
            $this,
            [
                'password' => Password::encodeToRequest(uniqid()),
                'current_password' => Password::encodeToRequest(uniqid()),
            ]
        )
            ->assertCode(Errors::UpdateFailed)
            ->assertMessage('当前密码错误');
    });
});

describe('update avatar', function () {
    it(updates('avatar'), function () {
        AccountRoute::UpdateAvatar->testing($this)
            ->withModel(Admin::class)
            ->withPayload([
                'file' => UploadedFile::fake()->image('avatar.jpg'),
            ])->updated(
                AdminFactory::testingAdmin(),
                fn (Admin $admin) => expect($admin->avatar)->toEndWith('.jpg')
            );
    });

    it('resets avatar successfully', function () {
        AccountRoute::UpdateAvatar->testing($this)
            ->withModel(Admin::class)
            ->withPayload([])
            ->updated(
                AdminFactory::testingAdmin(),
                fn (Admin $admin) => expect($admin->avatar)->toBe('')
            );
    });
});

it(updates('profile'), function ($avatar) {
    $payload = ['realname' => uniqid(), 'avatar' => $avatar];
    AccountRoute::UpdateProfile->testJson($this, $payload)
        ->assertUpdated();

    expect(AdminFactory::testingAdmin())
        ->realname->toBe($payload['realname'])
        ->avatar->toBe((string) $payload['avatar']);
})->with([
    'empty avatar' => null,
    'has avatar' => 'http://www.'.uniqid().'.com',
]);

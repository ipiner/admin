<?php

declare(strict_types=1);

use App\Models\System\Admin;
use App\Models\System\Menu;
use App\Routes\AccountRoute;
use Database\Factories\System\AdminFactory;
use Database\Factories\System\MenuFactory;
use Database\Factories\System\RoleFactory;
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

    it('fetches role-limited account profile with menus', function () {
        $menu = MenuFactory::new()->create([
            'pid' => 0,
            'type' => Menu::MENU,
            'enabled' => 1,
            'visible' => 1,
        ]);
        $role = RoleFactory::new()->create();
        $role->menus()->attach($menu);

        $admin = AdminFactory::new()->create();
        $admin->roles()->attach($role);
        $this->withAuth($admin);

        $data = AccountRoute::Profile->testJson($this, ['menus' => 1])->json('data');
        $menuIds = collect($data['menus'])->pluck('id')->all();

        expect($data['has_all_access'])->toBeFalse()
            ->and($menuIds)->toContain($menu->id);
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

it(updates('profile'), function () {
    $admin = AdminFactory::testingAdmin();
    $avatar = (string) $admin->avatar;
    $payload = ['realname' => uniqid()];

    AccountRoute::UpdateProfile->testJson($this, $payload)
        ->assertUpdated();

    expect($admin->refresh())
        ->realname->toBe($payload['realname'])
        ->avatar->toBe($avatar);
});

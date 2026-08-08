<?php

declare(strict_types=1);

use App\Models\System\Admin;
use App\Models\System\Role;
use App\Modules\System\Admin\Actions\UpdateAdminAction;
use App\Routes\System\AdminRoute;
use Database\Factories\System\AdminFactory;
use Database\Factories\System\RoleFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Pin\Support\Facades\Password;

describe('password', function () {
    it('updates password', function () {
        $admin = AdminFactory::testingAdmin();
        $plain = Str::random();
        $password = Password::encodeToRequest($plain);

        AdminRoute::Update->testing($this)->withPayload(
            UpdateAdminAction::fake(['username' => $admin->username, 'password' => $password])
        )->updated(
            $admin,
            fn (Admin $admin) => expect(
                Password::check(Password::encode($plain), $admin->salt, $admin->password)
            )->toBeTrue()
        );
    });
    it('does not update password when password is empty', function () {
        $admin = AdminFactory::testingAdmin();
        $oldPassword = $admin->password;
        AdminRoute::Update->testing($this)->withPayload(
            UpdateAdminAction::fake(['username' => $admin->username, 'password' => ''])
        )->updated(
            $admin,
            fn (Admin $admin) => expect($admin->password)->toBe($oldPassword),
        );
    });
});

describe('roles', function () {
    it('syncs roles', function () {
        $admin = AdminFactory::new()->create();
        expect($admin->roles()->get())->toBeEmpty();
        $role = RoleFactory::testingRole();
        AdminRoute::Update->testing($this)->withPayload(
            UpdateAdminAction::fake(['roles' => [$role->id]])
        )->updated(
            $admin,
            function (Admin $admin) use ($role) {
                $roles = $admin->roles()->get()->pluck('id')->toArray();
                expect($roles)->toHaveCount(1)
                    ->and($roles[0])->toBe($role->id);
            }
        );
    });
    it('only syncs super role when payload contains super role', function () {
        $admin = AdminFactory::new()->create();
        expect($admin->roles()->get())->toBeEmpty();
        $role = RoleFactory::testingRole();
        AdminRoute::Update->testing($this->withAuth(Admin::ADMINISTRATOR_ID))
            ->withPayload(
                UpdateAdminAction::fake(['roles' => [$role->id, Role::SUPER_ROLE_ID]])
            )
            ->updated(
                $admin,
                function (Admin $admin) {
                    $roles = $admin->roles()->get()->pluck('id')->toArray();
                    expect($roles)->toHaveCount(1)
                        ->and($roles[0])->toBe(Role::SUPER_ROLE_ID);
                }
            );
    });
    it('does not sync roles for super admin', function () {
        $admin = Admin::find(Admin::ADMINISTRATOR_ID);
        expect($admin->roles()->get())->toBeEmpty();
        $role = RoleFactory::testingRole();
        AdminRoute::Update->testing($this->withAuth(Admin::ADMINISTRATOR_ID))->withPayload(
            UpdateAdminAction::fake(['roles' => [$role->id, Role::SUPER_ROLE_ID]])
        )->updated(
            $admin,
            fn (Admin $admin) => expect($admin->roles()->get())->toBeEmpty()
        );
    });
});

it(updates("admin's avatar"), function () {
    $admin = AdminFactory::testingAdmin();
    AdminRoute::UpdateAvatar->testing($this)
        ->withRouteParams(['id' => $admin->id])
        ->withPayload([
            'file' => UploadedFile::fake()->image('avatar.jpg'),
        ])->updated(
            $admin,
            fn (Admin $admin) => expect($admin->avatar)->toEndWith('.jpg')
        );
});

it(validatesUpdateRequired('admin'), function () {
    $admin = AdminFactory::testingAdmin();
    AdminRoute::Update->testing($this)
        ->withRouteParams(['id' => $admin->id])
        ->json([])
        ->assertCode(422, 422)
        ->assertInvalid('username')
        ->assertValid('password');
});

it(ensuresUnique('admin', 'username'), function () {
    $admin = AdminFactory::new()->create();
    AdminRoute::Update->testing($this)
        ->withRouteParams(['id' => $admin->id])
        ->json(UpdateAdminAction::fake(['username' => AdminFactory::testingAdmin()->username]))
        ->assertInvalid('username')
        ->assertMessage('用户名已经存在');
});

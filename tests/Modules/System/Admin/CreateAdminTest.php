<?php

declare(strict_types=1);

use App\Models\System\Admin;
use App\Models\System\Role;
use App\Modules\System\Admin\Actions\CreateAdminAction;
use App\Routes\System\AdminRoute;
use Database\Factories\System\AdminFactory;
use Database\Factories\System\RoleFactory;

describe('roles', function () {
    it('attaches roles to admin', function () {
        $testingRole = RoleFactory::testingRole();
        $role = RoleFactory::new()->create();
        AdminRoute::Create->testing($this)->withPayload(
            CreateAdminAction::fake(['roles' => [$role->id, $testingRole->id]])
        )->created(
            fn (Admin $admin) => expect($admin->roles()->get())->toHaveCount(2)
        );
    });

    it('only attaches super role when payload contains super role', function () {
        $testingRole = RoleFactory::testingRole();
        AdminRoute::Create->testing($this->withAuth(Admin::ADMINISTRATOR_ID))->withPayload(
            CreateAdminAction::fake(['roles' => [Role::SUPER_ROLE_ID, $testingRole->id]])
        )->created(
            function (Admin $admin) {
                $roles = $admin->roles()->get()->pluck('id')->toArray();
                expect($roles)->toHaveCount(1)
                    ->and($roles[0])->toBe(Role::SUPER_ROLE_ID);
            }
        );
    });

    it('validates roles existence', function () {
        AdminRoute::Create->testing($this)
            ->json(CreateAdminAction::fake(['roles' => [-1]]))
            ->assertInvalid('roles')
            ->assertMessage('角色 [-1] 不存在');
    });

    it('forbids non-super admin from assigning super role', function () {
        AdminRoute::Create->testing($this)
            ->json(CreateAdminAction::fake(['roles' => [Role::SUPER_ROLE_ID]]))
            ->assertInvalid('roles')
            ->assertMessageMatch('/无权限分配角色/');
    });

    it('allows super admin to assign super role', function () {
        AdminRoute::Create->testing($this->withAuth(Admin::ADMINISTRATOR_ID))
            ->withPayload(CreateAdminAction::fake(['roles' => [Role::SUPER_ROLE_ID]]))
            ->created();
    });
});

it(validatesCreateRequired('admin'), function () {
    AdminRoute::Create->testJson($this)
        ->assertCode(422, 422)
        ->assertInvalid(['username', 'password']);
});

it(ensuresUnique('admin', 'username'), function () {
    AdminRoute::Create->testing($this)
        ->json(CreateAdminAction::fake(['username' => AdminFactory::testingAdmin()->username]))
        ->assertInvalid('username')
        ->assertMessage('用户名已经存在');
});

<?php

declare(strict_types=1);

use App\Models\System\Admin;
use App\Models\System\Role;
use App\Modules\System\Role\RoleGuard;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

describe('update role', function () {

    it('forbids updating super role by non-super role', function () {
        RoleGuard::ensureUpdatable(new Role());
        expect(true)->toBeTrue();
        RoleGuard::ensureUpdatable(
            new Role(['id' => Role::SUPER_ROLE_ID])
        );
    })->throws(Exception::class, '禁止修改该角色', Errors::UpdateFailed->code());

    it('allows super role to update self', function () {
        $this->withAuth(Admin::ADMINISTRATOR_ID);
        RoleGuard::ensureUpdatable(
            new Role(['id' => Role::SUPER_ROLE_ID])
        );
        expect(true)->toBeTrue();
    });
});

it('forbids deleting super role', function () {
    RoleGuard::ensureDeletable(new Role());
    expect(true)->toBeTrue();

    RoleGuard::ensureDeletable(
        new Role(['id' => Role::SUPER_ROLE_ID])
    );
})->throws(Exception::class, '禁止删除该角色', Errors::DeleteFailed->code());

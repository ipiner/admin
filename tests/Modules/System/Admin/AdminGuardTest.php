<?php

declare(strict_types=1);

use App\Models\System\Admin;
use App\Modules\System\Admin\AdminGuard;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

describe('update admin', function () {

    it('forbids updating super admin by non-super admin', function () {
        AdminGuard::ensureUpdatable(new Admin());
        expect(true)->toBeTrue();
        AdminGuard::ensureUpdatable(new Admin(['id' => Admin::ADMINISTRATOR_ID]));
    })->throws(Exception::class, '禁止修改该管理员', Errors::UpdateFailed->code());

    it('allows super admin to update self', function () {
        $admin = Admin::find(Admin::ADMINISTRATOR_ID);
        $this->withAuth($admin);
        AdminGuard::ensureUpdatable($admin);
        expect(true)->toBeTrue();
    });
});

it('forbids deleting super admin', function () {
    AdminGuard::ensureDeletable(new Admin());
    expect(true)->toBeTrue();

    AdminGuard::ensureDeletable(
        new Admin(['id' => Admin::ADMINISTRATOR_ID])
    );
})->throws(Exception::class, '禁止删除该管理员', Errors::DeleteFailed->code());

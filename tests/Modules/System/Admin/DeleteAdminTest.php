<?php

declare(strict_types=1);

use App\Models\System\Admin;
use App\Routes\System\AdminRoute;
use Pin\Errors\Errors;

it('forbids deleting super admin', function () {
    AdminRoute::Delete->testing($this)
        ->withRouteParams(['id' => Admin::ADMINISTRATOR_ID])
        ->json()
        ->assertMessage('禁止删除该管理员')
        ->assertCode(Errors::DeleteFailed->code());

    Admin::findOrFail(Admin::ADMINISTRATOR_ID);
});

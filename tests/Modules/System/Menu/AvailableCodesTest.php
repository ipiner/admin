<?php

declare(strict_types=1);

use App\Routes\AccountRoute;
use App\Routes\Auth\LoginRoute;
use App\Routes\System\MenuRoute;
use App\Routes\System\RoleRoute;

it('lists available codes', function () {
    $data = MenuRoute::AvailableCodes->testJson($this)->json('data');
    $data = collect($data)->pluck('value')->toArray();

    foreach ([
        MenuRoute::Delete->name(),
        RoleRoute::Delete->name(),
    ] as $name) {
        expect(in_array($name, $data))->toBeTrue();
    }

    foreach ([
        MenuRoute::AvailableCodes->name(),
        RoleRoute::Selector->name(),
        AccountRoute::UpdatePassword->name(),
        LoginRoute::Login->name(),
    ] as $name) {
        expect(in_array($name, $data))->toBeFalse();
    }

});

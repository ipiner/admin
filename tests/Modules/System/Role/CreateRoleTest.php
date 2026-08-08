<?php

declare(strict_types=1);

use App\Models\System\Role;
use App\Modules\System\Role\Actions\CreateRoleAction;
use App\Routes\System\RoleRoute;
use Database\Factories\System\MenuFactory;
use Database\Factories\System\RoleFactory;

describe('menus', function () {
    it('validates menus existence', function () {
        RoleRoute::Create->testing($this)
            ->json(CreateRoleAction::fake(['menus' => [-1, -2]]))
            ->assertInvalid('menus')
            ->assertMessage('菜单 [-1,-2] 不存在');
    });

    it('attaches menus to role', function () {
        $testingMenu = MenuFactory::testingMenu();
        $menu = MenuFactory::new()->create();
        RoleRoute::Create->testing($this)->withPayload(
            CreateRoleAction::fake(['menus' => [$menu->id, $testingMenu->id]])
        )->created(
            fn (Role $role) => expect($role->menus()->get())->toHaveCount(2)
        );
    });
});

it(validatesCreateRequired('role'), function () {
    RoleRoute::Create->testJson($this)
        ->assertCode(422, 422)
        ->assertInvalid(['name']);
});

it(ensuresUnique('role', 'name'), function () {
    RoleRoute::Create->testing($this)
        ->json(CreateRoleAction::fake(['name' => RoleFactory::testingRole()->name]))
        ->assertInvalid('name')
        ->assertMessage('名称已经存在');
});

<?php

declare(strict_types=1);

use App\Models\System\Role;
use App\Modules\System\Role\Actions\UpdateRoleAction;
use App\Routes\System\RoleRoute;
use Database\Factories\System\MenuFactory;
use Database\Factories\System\RoleFactory;

describe('menus', function () {
    it('syncs menus', function () {
        $role = RoleFactory::new()->create();
        expect($role->menus()->get())->toBeEmpty();
        $menu = MenuFactory::testingMenu();
        RoleRoute::Update->testing($this)->withPayload(
            UpdateRoleAction::fake(['menus' => [$menu->id]])
        )->updated(
            $role,
            function (Role $role) use ($menu) {
                $roles = $role->menus()->get()->pluck('id')->toArray();
                expect($roles)->toHaveCount(1)
                    ->and($roles[0])->toBe($menu->id);
            }
        );
    });

    it('does not sync menus for super role', function () {
        $role = Role::find(Role::SUPER_ROLE_ID);
        expect($role->menus()->get())->toBeEmpty();
        $menu = MenuFactory::testingMenu();
        RoleRoute::Update->testing($this->withAuth(Role::SUPER_ROLE_ID))
            ->withPayload(
                UpdateRoleAction::fake(['menus' => [$menu->id], 'v' => 1])
            )
            ->updated(
                $role,
                fn (Role $role) => expect($role->menus()->get())->toBeEmpty()
            );
    });
});

it(validatesUpdateRequired('role'), function () {
    $role = RoleFactory::testingRole();
    RoleRoute::Update->testing($this)
        ->withRouteParams(['id' => $role->id])
        ->json([])
        ->assertCode(422, 422)
        ->assertInvalid('name');
});

it(ensuresUnique('role', 'name'), function () {
    $role = RoleFactory::new()->create();
    RoleRoute::Update->testing($this)
        ->withRouteParams(['id' => $role->id])
        ->json(UpdateRoleAction::fake(['name' => RoleFactory::testingRole()->name]))
        ->assertInvalid('name')
        ->assertMessage('名称已经存在');
});

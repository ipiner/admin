<?php

declare(strict_types=1);

use App\Models\System\Menu;
use App\Modules\System\Menu\MenuGuard;
use App\Routes\System\MenuRoute;
use Pin\Errors\Errors;

describe('updating menu enabled status', function () {
    it('allows when next enabled value is null', function () {
        MenuGuard::ensureEnabledStatusChangeAllowed(new Menu(), null);
        expect(true)->toBeTrue();
    });

    it('disallows disabling special menu', function () {
        $menu = new Menu(['code' => MenuRoute::Index->name(), 'name' => 'test']);
        MenuGuard::ensureEnabledStatusChangeAllowed($menu, Menu::ENABLED);
        expect(true)->toBeTrue();

        MenuGuard::ensureEnabledStatusChangeAllowed($menu, Menu::DISABLED);
    })->throws(Exception::class, '[test] 不可禁用', Errors::UpdateFailed->code());

    it('disallows changing system-enabled menu', function () {
        $menu = new Menu([
            'code' => MenuRoute::Index->name(),
            'enabled' => Menu::SYSTEM_ENABLED,
            'name' => 'test',
        ]);
        MenuGuard::ensureEnabledStatusChangeAllowed($menu, Menu::SYSTEM_ENABLED);
        expect(true)->toBeTrue();

        MenuGuard::ensureEnabledStatusChangeAllowed($menu, Menu::ENABLED);
    })->throws(Exception::class, '禁止修改 [test] 启用状态', Errors::UpdateFailed->code());
});

it('forbids deleting menu', function () {
    MenuGuard::ensureDeletable(new Menu());
    expect(true)->toBeTrue();

    MenuGuard::ensureDeletable(
        new Menu(['code' => MenuRoute::Index->name(), 'name' => 'test'])
    );
})->throws(Exception::class, '禁止删除 [test]', Errors::DeleteFailed->code());

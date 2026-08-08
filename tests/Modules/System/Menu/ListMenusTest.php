<?php

declare(strict_types=1);

use App\Models\System\Menu;
use App\Routes\System\MenuRoute;
use Database\Factories\System\MenuFactory;

it('lists menus when paging is enabled', function () {
    $menu = MenuFactory::new()->create(['type' => Menu::MENU]);
    MenuRoute::Index->testing($this)->withPayload(['paging' => 1])
        ->paginated(function ($items, $total, $totalPage) {
            expect(count($items))->toBeGreaterThan(1)
                ->and($total)->toBeGreaterThan(1)
                ->and($totalPage)->toBeGreaterThanOrEqual(1);
        });

    $searches = [
        'q' => $menu->id,
        ' q ' => $menu->name,
        'code' => $menu->code,
        'route' => $menu->route,
    ];
    foreach ($searches as $type => $q) {
        MenuRoute::Index->testing($this)
            ->withPayload([trim($type) => $q, 'paging' => 1])
            ->paginated(function ($items, $total, $totalPage) use ($menu, $type) {
                expect($items)->toHaveCount(1, "searches by {$type}")
                    ->and($items[0]['id'])->toBe($menu->id)
                    ->and($total)->toBe(1)
                    ->and($totalPage)->toBe(1);
            });
    }
});

it('lists menus when paging is disabled', function () {
    MenuRoute::Index->testing($this)->withPayload(['page_size' => 1])
        ->paginated(function ($items, $total, $totalPage) {
            expect($items)->toHaveCount($total)
                ->and($totalPage)->toBe(1);
        });
});

<?php

declare(strict_types=1);

use App\Models\System\Menu;
use App\Modules\System\Menu\Actions\CreateMenuAction;
use App\Routes\System\MenuRoute;
use Database\Factories\System\MenuFactory;

it('lists menus when paging is enabled', function () {
    $suffix = bin2hex(random_bytes(6));
    $menu = MenuFactory::new()->create([
        'type' => Menu::MENU,
        'name' => "Testing menu {$suffix}",
        'code' => "testing_menu_{$suffix}",
        'url' => "/testing-menu-{$suffix}",
    ]);
    MenuFactory::new()->create(['type' => Menu::MENU]);

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
        'url' => $menu->url,
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

it('allows internal and external menu urls', function (string $prefix) {
    $url = $prefix.bin2hex(random_bytes(6));

    MenuRoute::Create->testing($this)->withPayload(
        CreateMenuAction::fake([
            'type' => Menu::MENU,
            'url' => $url,
        ])
    )->created(
        fn (Menu $menu) => expect($menu->url)->toBe($url)
    );
})->with([
    'internal' => ['/testing-menu-url-'],
    'external' => ['https://example.com/testing-menu-url-'],
]);

it('validates menu url format', function () {
    MenuRoute::Create->testing($this)
        ->json(CreateMenuAction::fake([
            'type' => Menu::MENU,
            'url' => 'testing-menu-url',
        ]))
        ->assertInvalid('url');
});

it('lists menus when paging is disabled', function () {
    MenuFactory::new()->create(['type' => Menu::MENU]);

    MenuRoute::Index->testing($this)->withPayload(['page_size' => 1])
        ->paginated(function ($items, $total, $totalPage) {
            expect($items)->toHaveCount($total)
                ->and($totalPage)->toBe(1);
        });
});

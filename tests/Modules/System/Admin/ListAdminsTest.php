<?php

declare(strict_types=1);

use App\Routes\System\AdminRoute;
use Database\Factories\System\AdminFactory;

it(lists('admin'), function () {
    $admin = AdminFactory::new()->create();
    AdminRoute::Index->testing($this)->paginated(function ($items, $total) {
        expect(count($items))->toBeGreaterThan(1)
            ->and($total)->toBeGreaterThan(1);
    });

    $searches = [
        'id' => $admin->id,
        'username' => $admin->username,
        'realname' => $admin->realname,
    ];
    foreach ($searches as $type => $q) {
        AdminRoute::Index->testing($this)->withPayload(['q' => $q])
            ->paginated(function ($items, $total) use ($admin, $type) {
                expect($items)->toHaveCount(1, "searches by {$type}")
                    ->and($items[0]['id'])->toBe($admin->id)
                    ->and($total)->toBe(1);
            });
    }
});

<?php

declare(strict_types=1);

use App\Modules\Content\Routes\ArticleCategoryRoute;
use Database\Factories\Content\ArticleCategoryFactory;

it('lists categories when paging is enabled', function () {
    $category = ArticleCategoryFactory::new()->create();
    ArticleCategoryRoute::Index->testing($this)->withPayload(['paging' => 1])
        ->paginated(function ($items, $total, $totalPage) {
            expect(count($items))->toBeGreaterThan(1)
                ->and($total)->toBeGreaterThan(1)
                ->and($totalPage)->toBeGreaterThanOrEqual(1);
        });

    $searches = [
        'q' => $category->id,
        ' q ' => $category->name,
    ];
    foreach ($searches as $type => $q) {
        ArticleCategoryRoute::Index->testing($this)
            ->withPayload([trim($type) => $q, 'paging' => 1])
            ->paginated(function ($items, $total, $totalPage) use ($category, $type) {
                expect($items)->toHaveCount(1, "searches by {$type}")
                    ->and($items[0]['id'])->toBe($category->id)
                    ->and($total)->toBe(1)
                    ->and($totalPage)->toBe(1);
            });
    }
});

it('lists categories when paging is disabled', function () {
    ArticleCategoryRoute::Index->testing($this)->withPayload(['page_size' => 1])
        ->paginated(function ($items, $total, $totalPage) {
            expect($items)->toHaveCount($total)
                ->and($totalPage)->toBe(1);
        });
});

<?php

declare(strict_types=1);

use App\Modules\Content\Routes\ArticleRoute;
use Database\Factories\Content\ArticleFactory;

it(lists('article'), function () {
    $article = ArticleFactory::new()->create();
    ArticleRoute::Index->testing($this)->paginated(function ($items, $total) {
        expect(count($items))->toBeGreaterThan(1)
            ->and($total)->toBeGreaterThan(1);
    });

    $searches = [
        'id' => $article->id,
        'title' => $article->title,
        'content' => $article->content,
    ];
    foreach ($searches as $type => $q) {
        ArticleRoute::Index->testing($this)
            ->withPayload(['q' => $q, 'category_id' => $article->category_id])
            ->paginated(function ($items, $total) use ($article, $type) {
                expect($items)->toHaveCount(1, "searches by {$type}")
                    ->and($items[0]['id'])->toBe($article->id)
                    ->and($total)->toBe(1);
            });
    }
});

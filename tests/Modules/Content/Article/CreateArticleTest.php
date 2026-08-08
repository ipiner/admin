<?php

declare(strict_types=1);

use App\Modules\Content\Article\Actions\CreateArticleAction;
use App\Modules\Content\Routes\ArticleRoute;
use Database\Factories\Content\ArticleFactory;

it('ensures category exist', function () {
    ArticleRoute::Create->testing($this)
        ->json(CreateArticleAction::fake(['category_id' => -1]))
        ->assertInvalid('category_id')
        ->assertMessage('分类 [-1] 不存在');
});

it(validatesCreateRequired('article'), function () {
    ArticleRoute::Create->testJson($this)
        ->assertCode(422, 422)
        ->assertInvalid(['title', 'content', 'category_id']);
});

it(ensuresUnique('article', 'title'), function () {
    ArticleRoute::Create->testing($this)
        ->json(CreateArticleAction::fake(['title' => ArticleFactory::testingArticle()->title]))
        ->assertInvalid('title')
        ->assertMessage('标题已经存在');
});

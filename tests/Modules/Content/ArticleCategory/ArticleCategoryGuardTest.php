<?php

declare(strict_types=1);

use App\Modules\Content\ArticleCategory\ArticleCategoryGuard;
use App\Modules\Content\Models\ArticleCategory;
use Database\Factories\Content\ArticleCategoryFactory;
use Database\Factories\Content\ArticleFactory;
use Pin\Errors\Errors;

it('forbids deleting default category', function () {
    ArticleCategoryGuard::ensureDeletable(new ArticleCategory());
    expect(true)->toBeTrue();
    ArticleCategoryGuard::ensureDeletable(
        new ArticleCategory(['id' => ArticleCategory::DEFAULT_ID, 'name' => 'test'])
    );
})->throws(Exception::class, '禁止删除 [test]', Errors::DeleteFailed->code());

it('forbids deleting category with articles', function () {
    $category = ArticleCategoryFactory::new()->create();
    ArticleFactory::new()->create(['category_id' => $category->id]);
    expect(true)->toBeTrue();
    ArticleCategoryGuard::ensureDeletable($category);
})->throws(Exception::class, '分类下存在文章，无法删除', Errors::DeleteFailed->code());

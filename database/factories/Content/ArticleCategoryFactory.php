<?php

declare(strict_types=1);

namespace Database\Factories\Content;

use App\Modules\Content\ArticleCategory\Actions\CreateArticleCategoryAction;
use App\Modules\Content\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Cache;

/**
 * @extends Factory<ArticleCategory>
 */
class ArticleCategoryFactory extends Factory
{
    protected $model = ArticleCategory::class;

    public function definition(): array
    {
        return CreateArticleCategoryAction::fake();
    }

    public static function testingArticleCategory(): ArticleCategory
    {
        return Cache::lock('article.category.testing.create', 60)->block(5, function () {
            if ($item = ArticleCategory::where('name', 'testing')->where('pid', 0)->first()) {
                return $item;
            }

            return static::new()->create(['name' => 'testing']);
        });
    }
}

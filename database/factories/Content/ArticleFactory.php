<?php

declare(strict_types=1);

namespace Database\Factories\Content;

use App\Modules\Content\Article\Actions\CreateArticleAction;
use App\Modules\Content\Models\Article;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Cache;

/**
 * @extends Factory<Article>
 */
class ArticleFactory extends Factory
{
    protected $model = Article::class;

    public function definition(): array
    {
        return CreateArticleAction::fake();
    }

    public static function testingArticle(): Article
    {
        return Cache::lock('article.testing.create', 60)->block(5, function () {
            if ($item = Article::where('title', 'testing')->first()) {
                return $item;
            }

            return static::new()->create(['title' => 'testing']);
        });
    }
}

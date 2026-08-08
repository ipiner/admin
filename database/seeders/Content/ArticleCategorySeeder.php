<?php

declare(strict_types=1);

namespace Database\Seeders\Content;

use App\Modules\Content\Models\ArticleCategory;
use Illuminate\Database\Seeder;

class ArticleCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        ArticleCategory::create([
            'id' => ArticleCategory::DEFAULT_ID,
            'name' => '默认分类',
        ]);
    }
}

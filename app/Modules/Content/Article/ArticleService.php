<?php

declare(strict_types=1);

namespace App\Modules\Content\Article;

use App\Modules\Content\Models\Article;
use Pin\Services\ModelService;

/**
 * @extends ModelService<Article>
 */
class ArticleService extends ModelService
{
    public string $resourceName = '文章';
}

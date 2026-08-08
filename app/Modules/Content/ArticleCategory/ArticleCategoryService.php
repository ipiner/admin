<?php

declare(strict_types=1);

namespace App\Modules\Content\ArticleCategory;

use App\Modules\Content\Models\ArticleCategory;
use Pin\Tree\ModelService;

/**
 * @extends ModelService<ArticleCategory>
 */
class ArticleCategoryService extends ModelService
{
    public string $resourceName = '分类';

    /**
     * @param  ArticleCategory  $model
     */
    protected function deleting($model): void
    {
        ArticleCategoryGuard::ensureDeletable($model);
        parent::deleting($model);
    }
}

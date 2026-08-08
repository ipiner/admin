<?php

declare(strict_types=1);

namespace App\Modules\Content\Models;

use App\Models\Model;
use Pin\Models\Concerns\HasBlameable;
use Pin\Models\Concerns\SoftDeletes;

/**
 * 文章模型
 *
 * @mixin IdeHelperArticle
 */
class Article extends Model
{
    use HasBlameable, SoftDeletes;

    /**
     * 获取当前文章分类
     *
     * 如果分类不存在，则使用默认分类兜底
     */
    public function category(): ArticleCategory
    {
        return ArticleCategory::find($this->category_id)
            ?? ArticleCategory::findOrFail(ArticleCategory::DEFAULT_ID);
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Content\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Pin\Models\Concerns\CacheAll;
use Pin\Models\Concerns\HasBlameable;
use Pin\Models\Concerns\SoftDeletes;
use Pin\Modules\Log\Models\Concerns\HasOperationLog;
use Pin\Tree\TreeModel;

/**
 * 文章分类模型
 *
 * @mixin IdeHelperArticleCategory
 */
class ArticleCategory extends TreeModel
{
    use CacheAll, HasBlameable, HasOperationLog, SoftDeletes;

    /**
     * 默认分类id
     */
    public const int DEFAULT_ID = 1;

    /**
     * 是否默认分类
     */
    public function isDefault(): bool
    {
        return $this->id === self::DEFAULT_ID;
    }

    /**
     * 分类文章
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class, 'category_id');
    }

    /**
     * 返回操作日志中用于展示主体名称的字段。
     */
    public function subjectNameColumn(): string
    {
        return 'name';
    }
}

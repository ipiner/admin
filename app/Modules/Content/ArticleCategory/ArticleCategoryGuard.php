<?php

declare(strict_types=1);

namespace App\Modules\Content\ArticleCategory;

use App\Modules\Content\Models\ArticleCategory;
use Pin\Errors\Errors;
use Pin\Exceptions\Exception;

/**
 * 分类守卫
 *
 * 分类业务相关的权限约束和特殊分类保护逻辑
 */
class ArticleCategoryGuard
{
    /**
     * 是否允许删除
     *
     * @throws Exception
     */
    public static function ensureDeletable(ArticleCategory $category): void
    {
        if ($category->isDefault()) {
            Errors::DeleteFailed->throw("禁止删除 [{$category->name}]");
        }

        if ($category->articles()->exists()) {
            Errors::DeleteFailed->throw('分类下存在文章，无法删除');

        }
    }
}

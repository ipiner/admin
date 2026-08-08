<?php

declare(strict_types=1);

namespace App\Modules\Content\Routes;

use App\Routes\InteractsWithRoute;
use Pin\Access\Attributes\Access;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 文章分类接口路由定义
 */
enum ArticleCategoryRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('文章分类')]
    case Index = 'GET:/api/content/article-categories';

    #[Title('新增文章分类')]
    case Create = 'POST:/api/content/article-categories';

    #[Title('编辑文章分类')]
    case Update = 'PUT:/api/content/article-categories/{id}';

    #[Title('删除文章分类')]
    case Delete = 'DELETE:/api/content/article-categories/{id}';

    /**
     * 文章分类下拉框选择器
     *
     * - `新增` / `编辑` 文章分类时的上级文章分类下拉选择器选项
     * - `新增` / `编辑` 角色时的权限下拉选择器选项
     */
    #[Title('文章分类下拉框选择器')]
    #[Access(self::Index)]
    case Selector = 'GET:/api/content/article-categories/selector';
}

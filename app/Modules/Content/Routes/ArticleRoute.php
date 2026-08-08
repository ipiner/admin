<?php

declare(strict_types=1);

namespace App\Modules\Content\Routes;

use App\Routes\InteractsWithRoute;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 文章接口路由定义
 */
enum ArticleRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('文章')]
    case Index = 'GET:/api/content/articles';

    #[Title('新增文章')]
    case Create = 'POST:/api/content/articles';

    #[Title('编辑文章')]
    case Update = 'PUT:/api/content/articles/{id}';

    #[Title('删除文章')]
    case Delete = 'DELETE:/api/content/articles/{id}';
}

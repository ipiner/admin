<?php

declare(strict_types=1);

namespace Database\Seeders\System\Concerns;

use App\Modules\Content\Routes\ArticleCategoryRoute;
use App\Modules\Content\Routes\ArticleRoute;

trait HasContentMenus
{
    protected function content(): array
    {
        return [
            'name' => '内容',
            'code' => 'content',
            'icon' => 'ant-design:appstore-outlined',
            'route' => '/content',
            'children' => [
                [
                    'icon' => 'ant-design:file-text-outlined',
                    'route' => ArticleRoute::Index,
                    'children' => [
                        ArticleRoute::Update,
                        ArticleRoute::Delete,
                    ],
                ],
                [
                    'icon' => 'ant-design:folder-outlined',
                    'route' => ArticleCategoryRoute::Index,
                    'children' => [
                        ArticleCategoryRoute::Update,
                        ArticleCategoryRoute::Delete,
                    ],
                ],
            ],
        ];
    }
}

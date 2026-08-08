<?php

declare(strict_types=1);

namespace App\Modules\Content\Article;

use App\Modules\Content\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * 文章列表响应资源。
 *
 * @mixin Article
 */
class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $category = $this->category();

        return array_merge(
            parent::toArray($request),
            [
                // 分类
                'category' => [
                    /**
                     * @var string
                     */
                    'name' => $category->namePath(' > '),
                    /**
                     * @var int[]
                     */
                    'paths' => $category->paths,
                ],
            ]
        );
    }
}

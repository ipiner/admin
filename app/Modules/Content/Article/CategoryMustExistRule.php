<?php

declare(strict_types=1);

namespace App\Modules\Content\Article;

use App\Modules\Content\Models\ArticleCategory;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * 校验文章分类必须存在。
 */
class CategoryMustExistRule implements ValidationRule
{
    /**
     * {@inheritDoc}
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $id = (int) $value;

        if (! ArticleCategory::find($id)) {
            $fail('分类 ['.$id.'] 不存在');
        }
    }
}

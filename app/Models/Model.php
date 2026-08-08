<?php

declare(strict_types=1);

namespace App\Models;

use Pin\Modules\Log\Models\Concerns\HasOperationLog;

/**
 * 后台模型基类，统一接入操作日志和隐藏删除字段。
 *
 * @mixin IdeHelperModel
 */
class Model extends \Pin\Models\Model
{
    use HasOperationLog;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->hidden[] = 'deleted_at';
    }
}

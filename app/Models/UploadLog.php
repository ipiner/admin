<?php

declare(strict_types=1);

namespace App\Models;

/**
 * 上传日志。
 *
 * @mixin IdeHelperUploadLog
 */
class UploadLog extends Model
{
    protected $casts = [
        'info' => 'array',
    ];
}

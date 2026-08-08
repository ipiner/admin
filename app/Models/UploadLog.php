<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Upload\IdeHelperUploadLog;

/**
 * @mixin IdeHelperUploadLog
 */
class UploadLog extends Model
{
    protected $casts = [
        'info' => 'array',
    ];
}

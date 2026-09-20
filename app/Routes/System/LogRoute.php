<?php

declare(strict_types=1);

namespace App\Routes\System;

use App\Modules\System\Log\UploadLogController;
use App\Routes\InteractsWithRoute;
use Pin\Access\Attributes\Access;
use Pin\Route\Attributes\Handler;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 日志路由。
 */
enum LogRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('上传日志')]
    #[Handler([UploadLogController::class, 'index'])]
    case UploadLog = 'GET:/api/system/log/uploads';

    #[Title('上传日志筛选项')]
    #[Handler([UploadLogController::class, 'options'])]
    #[Access(self::UploadLog)]
    case UploadLogOption = 'GET:/api/system/log/uploads/options';
}

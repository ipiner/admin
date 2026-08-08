<?php

declare(strict_types=1);

namespace App\Routes\System;

use App\Modules\System\Log\UploadLogController;
use App\Routes\InteractsWithRoute;
use Pin\Access\Attributes\Access;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 日志接口路由定义
 */
enum LogRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('上传日志')]
    case UploadLog = 'GET:/api/system/log/uploads';

    #[Title('上传日志筛选项')]
    #[Access(self::UploadLog)]
    case UploadLogOption = 'GET:/api/system/log/uploads/options';

    /**
     * 注册路由
     */
    public static function registerRoutes(): void
    {
        self::UploadLog->register([UploadLogController::class, 'index']);
        self::UploadLogOption->register([UploadLogController::class, 'options']);
    }
}

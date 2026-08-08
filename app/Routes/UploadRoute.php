<?php

declare(strict_types=1);

namespace App\Routes;

use App\Modules\Upload\UploadMiddleware;
use Pin\Access\Attributes\Access;
use Pin\Route\Attributes\Middleware;
use Pin\Route\Attributes\Title;
use Pin\Route\Routable;

/**
 * 上传接口路由定义
 */
enum UploadRoute: string implements Routable
{
    use InteractsWithRoute;

    #[Title('上传图片')]
    #[Access(false)]
    #[Middleware(UploadMiddleware::class)]
    case Image = 'POST:/api/uploads/image';
}

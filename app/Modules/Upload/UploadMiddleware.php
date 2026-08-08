<?php

declare(strict_types=1);

namespace App\Modules\Upload;

use App\Models\Upload;
use Closure;
use Illuminate\Http\Request;
use Pin\Upload\UploadedFile;

/**
 * 请求结束后持久化本次请求产生的上传文件记录。
 */
class UploadMiddleware
{
    /**
     * 上传处理发生在业务代码中，本中间件仅负责终止阶段记录。
     */
    public function handle(Request $request, Closure $next): mixed
    {
        return $next($request);
    }

    /**
     * 将 UploadedFile 收集器中的文件写入上传记录表。
     */
    public function terminate(): void
    {
        foreach (UploadedFile::items() as $file) {
            Upload::createFromUploadedFile($file);
        }
    }
}

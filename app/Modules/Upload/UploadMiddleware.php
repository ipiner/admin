<?php

declare(strict_types=1);

namespace App\Modules\Upload;

use App\Models\Upload;
use Closure;
use Illuminate\Http\Request;

/**
 * 上传记录中间件
 */
class UploadMiddleware
{
    /**
     * 处理请求
     */
    public function handle(Request $request, Closure $next): mixed
    {
        return $next($request);
    }

    /**
     * 保存上传记录
     */
    public function terminate(Request $request): void
    {
        $files = $request->attributes->get('uploaded-files', []);
        $request->attributes->remove('uploaded-files');

        foreach ($files as $file) {
            Upload::createFromUploadedFile($file);
        }
    }
}

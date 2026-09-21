<?php

declare(strict_types=1);

namespace App\Modules\Upload;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;
use Pin\Upload\UploadedFile;

/**
 * 上传接口
 */
#[Group('上传')]
class UploadController extends Controller
{
    /**
     * 上传图片
     *
     * @return ApiResponse<array{url: string}>
     */
    public function image(Request $request, UploadService $service): ApiResponse
    {
        $request->validate([
            // 图片文件
            'file' => 'required|file',
        ]);

        $file = $service->upload(
            $request,
            beforeStore: static fn (UploadedFile $file) => $file->thumb(true, 'l'),
        );

        return $this->success(['url' => $file->url()], '上传成功');
    }
}

<?php

declare(strict_types=1);

namespace App\Modules\Upload;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Illuminate\Http\Request;
use Pin\Http\ApiResponse;

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
        // 这里专给scramble解析body用，真正验证在Upload中
        $request->validate([
            // 图片文件
            'file' => 'file',
        ]);

        $file = $service->upload($request);
        $file->thumb(true, 'l');

        return $this->success(['url' => $file->url()], '上传成功');
    }
}

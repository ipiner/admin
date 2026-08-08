<?php

declare(strict_types=1);

namespace App\Modules\Upload;

use Illuminate\Http\Request;
use Pin\Upload\Rules\Upload as UploadRule;
use Pin\Upload\UploadedFile;

/**
 * 统一处理后台上传验证、存储目录和上传文件对象返回。
 */
class UploadService
{
    /**
     * 验证请求中的上传文件并保存到日期目录。
     */
    public function upload(
        Request $request,
        ?string $category = null,
        ?UploadRule $rule = null,
        string $name = 'file'
    ): UploadedFile {
        $data = $request->validate($this->uploadRules($rule, $name));
        $file = UploadedFile::item($data[$name]);
        $file->storeAs(($category ?: '').date('/Ym/d'));

        return $file;
    }

    /**
     * 构造上传字段的验证规则。
     */
    protected function uploadRules(?UploadRule $rule, string $name = 'file'): array
    {
        return [
            $name => ['required', $rule ?: new UploadRule()->disk('upload')],
        ];
    }
}

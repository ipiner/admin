<?php

declare(strict_types=1);

namespace App\Modules\Upload;

use Closure;
use Illuminate\Http\Request;
use Pin\Errors\Errors;
use Pin\Upload\Rules\Upload as UploadRule;
use Pin\Upload\UploadedFile;
use Throwable;

/**
 * 文件上传服务
 */
class UploadService
{
    /**
     * 验证并保存上传文件
     *
     * @param  (Closure(UploadedFile): void)|null  $beforeStore
     */
    public function upload(
        Request $request,
        ?string $category = null,
        ?UploadRule $rule = null,
        string $name = 'file',
        ?Closure $beforeStore = null
    ): UploadedFile {
        $request->validate($this->uploadRules($rule, $name));
        $file = UploadedFile::item($request->file($name));

        try {
            if ($beforeStore) {
                $beforeStore($file);
            }

            if (! $file->storeAs($this->directory($category))) {
                Errors::ServerError->throw('文件上传失败');
            }
        } catch (Throwable $e) {
            $file->errors = [Errors::ServerError->code() => '文件上传失败'];

            throw $e;
        }

        return $file;
    }

    /**
     * 上传目录
     */
    protected function directory(?string $category): string
    {
        return ltrim(trim($category ?? '', '/').now()->format('/Ym/d'), '/');
    }

    /**
     * 上传验证规则
     */
    protected function uploadRules(?UploadRule $rule, string $name = 'file'): array
    {
        return [
            $name => ['required', $rule ?? new UploadRule()->disk('upload')],
        ];
    }
}

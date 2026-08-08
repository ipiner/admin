<?php

declare(strict_types=1);

namespace App\Models;

use Pin\Log\Payload;
use Pin\Upload\UploadedFile;

/**
 * 上传文件主表模型。
 *
 * @mixin IdeHelperUpload
 */
class Upload extends Model
{
    protected $perPage = 18;

    protected $casts = [
        'info' => 'array',
    ];

    /**
     * 根据上传文件对象创建主记录和上传日志。
     */
    public static function createFromUploadedFile(UploadedFile $file): void
    {
        static::createUploadLogs($file, static::createUpload($file));
    }

    /**
     * 生成上传记录数据；上传失败时只返回数据用于写日志。
     */
    protected static function createUpload(UploadedFile $file): array
    {
        $payload = new Payload();
        $errors = $file->getErrors();

        $data = [
            'file_id' => $file->file_id,
            'name' => $file->original['name'],
            'original_name' => $file->original['name'],
            'path' => $file->path,
            'extension' => $file->extension,
            'mime_type' => $file->mime_type,
            'width' => (int) $file->width,
            'height' => (int) $file->height,
            'size' => $file->size,
            'info' => [
                'original' => $file->original,
                'thumb' => $file->thumb,
                'water' => $file->water,
                'errors' => $errors,
            ],
            'disk' => $file->disk ?: '',
            'uid' => $payload->uid,
            'username' => $payload->username,
            'user_type' => $payload->user_type,
            'ip' => $payload->ip,
        ];

        unset($data['info']['thumb']['pathname'], $data['info']['water']['pathname']);

        if (! $file->errors) {
            $data['url'] = $file->url();
            static::create($data);
        }

        return $data;
    }

    /**
     * 记录上传结果，成功 code 为 0，失败使用首个错误码。
     */
    protected static function createUploadLogs(UploadedFile $file, array $data): UploadLog
    {
        return UploadLog::create(array_merge(
            $data,
            [
                'code' => $file->errors ? array_key_first($data['info']['errors']) : 0,
                'message' => $file->errors ? array_first($data['info']['errors']) : '上传成功',
            ]
        ));
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Support\Arr;
use Pin\Log\Payload;
use Pin\Upload\UploadedFile;

/**
 * 上传文件。
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
     * 保存上传记录和日志。
     */
    public static function createFromUploadedFile(UploadedFile $file): void
    {
        $data = static::uploadData($file);

        if (! $data['info']['errors']) {
            $data['url'] = $file->url();
            static::create($data);
        }

        static::createUploadLog($data);
    }

    /**
     * 生成上传数据。
     *
     * @return array<string, mixed>
     */
    protected static function uploadData(UploadedFile $file): array
    {
        $payload = new Payload();

        return [
            'file_id' => $file->file_id,
            'name' => $file->original['name'],
            'original_name' => $file->original['name'],
            'path' => $file->path,
            'extension' => $file->extension,
            'mime_type' => $file->mime_type ?? '',
            'width' => (int) $file->width,
            'height' => (int) $file->height,
            'size' => $file->size,
            'info' => static::uploadInfo($file),
            'disk' => $file->disk ?: '',
            'uid' => $payload->uid,
            'username' => $payload->username,
            'user_type' => $payload->user_type,
            'ip' => $payload->ip,
        ];
    }

    /**
     * 整理文件信息。
     *
     * @return array<string, mixed>
     */
    protected static function uploadInfo(UploadedFile $file): array
    {
        return [
            'original' => $file->original,
            'thumb' => $file->thumb
                ? array_map(fn (array $thumb) => Arr::except($thumb, 'pathname'), $file->thumb)
                : $file->thumb,
            'water' => $file->water ? Arr::except($file->water, 'pathname') : $file->water,
            'errors' => $file->getErrors(),
        ];
    }

    /**
     * 记录上传结果。
     *
     * @param  array<string, mixed>  $data
     */
    protected static function createUploadLog(array $data): UploadLog
    {
        $errors = $data['info']['errors'];

        return UploadLog::create([
            ...$data,
            'code' => $errors ? array_key_first($errors) : 0,
            'message' => $errors ? array_first($errors) : '上传成功',
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\System\Admin;
use App\Models\Upload;
use App\Models\UploadLog;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Storage;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Support\Facades\Actor;
use Pin\Upload\UploadedFile;

class UploadTest extends ModelTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate('2026_03_29_165754_create_uploads_table.php');
        Storage::fake('model-uploads');
        Actor::shouldReceive('user')->andReturn(new Admin(['id' => 5, 'username' => 'uploader']));
        Actor::shouldReceive('type')->andReturn('admin');
    }

    public function testStoresUploadAndLogWithoutLocalPreviewPaths(): void
    {
        $file = $this->file();
        $file->storeAs('images', 'image.png');
        $file->thumb = [
            'small' => ['pathname' => '/private/small.png', 'path' => 'images/small.png'],
            'large' => ['pathname' => '/private/large.png', 'path' => 'images/large.png'],
        ];
        $file->water = ['pathname' => '/private/water.png', 'path' => 'images/water.png'];

        Upload::createFromUploadedFile($file);

        $this->assertSame(1, Upload::query()->count());
        $this->assertSame(1, UploadLog::query()->count());
        $upload = Upload::query()->firstOrFail();
        $log = UploadLog::query()->firstOrFail();

        $this->assertSame($upload->info, $log->info);
        $this->assertSame([
            'small' => ['path' => 'images/small.png'],
            'large' => ['path' => 'images/large.png'],
        ], $upload->info['thumb']);
        $this->assertSame(['path' => 'images/water.png'], $upload->info['water']);
        $this->assertSame('/private/small.png', $file->thumb['small']['pathname']);
        $this->assertSame('/private/water.png', $file->water['pathname']);
        $this->assertSame(5, $upload->uid);
        $this->assertSame('uploader', $upload->username);
        $this->assertSame('admin', $upload->user_type);
        $this->assertSame('images/image.png', $upload->path);
        $this->assertSame($file->url(), $upload->url);
        $this->assertSame($upload->url, $log->url);
        $this->assertSame(24, $upload->width);
        $this->assertSame(16, $upload->height);
        $this->assertSame(0, $log->code);
        $this->assertSame('上传成功', $log->message);
    }

    #[DataProvider('mimeTypes')]
    public function testRecordsOnlyLogForFailedUpload(?string $mimeType): void
    {
        $file = $this->file([3001 => 'First error', 3002 => 'Second error']);
        $file->mime_type = $mimeType;
        $file->uploadConfig['disk'] = 'unconfigured-disk';
        $file->disk = 'unconfigured-disk';

        Upload::createFromUploadedFile($file);

        $this->assertSame(0, Upload::query()->count());
        $this->assertSame(1, UploadLog::query()->count());
        $log = UploadLog::query()->firstOrFail();
        $this->assertSame(3001, $log->code);
        $this->assertSame('First error', $log->message);
        $this->assertSame('', $log->url);
        $this->assertSame($mimeType ?? '', $log->mime_type);
        $this->assertNull($log->info['thumb']);
        $this->assertNull($log->info['water']);
        $this->assertSame([3001 => 'First error', 3002 => 'Second error'], $log->info['errors']);
    }

    public static function mimeTypes(): array
    {
        return [['image/png'], [null]];
    }

    protected function file(array $errors = []): UploadedFile
    {
        return new UploadedFile(
            HttpUploadedFile::fake()->image('image.png', 24, 16),
            $errors,
            ['disk' => 'model-uploads']
        );
    }
}

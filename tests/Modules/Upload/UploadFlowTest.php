<?php

declare(strict_types=1);

namespace Tests\Modules\Upload;

use App\Models\System\Admin;
use App\Models\Upload;
use App\Models\UploadLog;
use App\Modules\Upload\UploadController;
use App\Modules\Upload\UploadMiddleware;
use App\Modules\Upload\UploadService;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile as HttpUploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\FilesystemAdapter as FlysystemAdapter;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Errors\Errors;
use Pin\Support\Facades\Actor;
use Pin\Upload\Errors as UploadErrors;
use Pin\Upload\Rules\Upload as UploadRule;
use Pin\Upload\UploadedFile;
use RuntimeException;
use Tests\Models\ModelTestCase;

class UploadFlowTest extends ModelTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate('2026_03_29_165754_create_uploads_table.php');
        config([
            'cache.default' => 'array',
            'pin.upload.thumb.l' => ['width' => 40, 'height' => null],
        ]);
        Storage::fake('upload');
        Storage::fake('documents');
        Mail::fake();
        Actor::shouldReceive('user')->andReturn(new Admin(['id' => 5, 'username' => 'uploader']));
        Actor::shouldReceive('type')->andReturn('admin');

        Route::post('/_tests/uploads/image', [UploadController::class, 'image'])
            ->middleware(UploadMiddleware::class);
        Route::post('/_tests/uploads/document', function (
            Request $request,
            UploadService $service
        ) {
            $file = $service->upload(
                $request,
                category: '/documents/',
                rule: new UploadRule()->disk('documents')->extensions('txt'),
                name: 'payload.document',
            );

            return response()->json(['path' => $file->path, 'url' => $file->url()]);
        })->middleware(UploadMiddleware::class);
    }

    public function testUploadsResizedImageAndRecordsMetadata(): void
    {
        $url = $this->post('/_tests/uploads/image', [
            'file' => HttpUploadedFile::fake()->image('image.png', 80, 40),
        ], ['Accept' => 'application/json'])
            ->assertSuccessful()->assertJsonPath('message', '上传成功')->json('data.url');

        $upload = Upload::query()->firstOrFail();
        $log = UploadLog::query()->firstOrFail();
        $this->assertSame($url, $upload->url);
        $this->assertSame($url, $log->url);
        $this->assertSame(40, $upload->width);
        $this->assertSame(20, $upload->height);
        $this->assertSame(80, $upload->info['original']['width']);
        $this->assertSame(40, $upload->info['original']['height']);
        $this->assertSame(5, $log->uid);
        $this->assertSame(0, $log->code);
        $this->assertSame($upload->info, $log->info);
        $this->assertStringStartsWith(now()->format('Ym/d/'), $upload->path);
        $dimensions = getimagesize(Storage::disk('upload')->path($upload->path));
        $this->assertSame([40, 20], array_slice($dimensions, 0, 2));
        $this->assertCount(1, Storage::disk('upload')->allFiles());
    }

    public function testResizesImageBeforeWritingToRemoteDisk(): void
    {
        $contents = null;
        $disk = $this->remoteDisk();
        $disk->shouldReceive('putFileAs')->once()->andReturnUsing(
            static function ($path, $file, $name) use (&$contents): string {
                $contents = file_get_contents($file->getPathname());

                return trim($path.'/'.$name, '/');
            }
        );
        Storage::set('upload', $disk);

        $this->post('/_tests/uploads/image', [
            'file' => HttpUploadedFile::fake()->image('image.png', 80, 40),
        ], ['Accept' => 'application/json'])->assertSuccessful();

        $this->assertSame([40, 20], array_slice(getimagesizefromstring($contents), 0, 2));
        $this->assertSame(40, Upload::query()->firstOrFail()->width);
        $this->assertSame(0, UploadLog::query()->firstOrFail()->code);
    }

    #[DataProvider('storageFailures')]
    public function testRecordsStorageFailure(bool $throws): void
    {
        $disk = $this->remoteDisk();
        $write = $disk->shouldReceive('putFileAs')->once();
        if ($throws) {
            $write->andThrow(new RuntimeException('Storage unavailable'));
        } else {
            $write->andReturn(false);
        }
        Storage::set('upload', $disk);

        $this->post('/_tests/uploads/image', [
            'file' => HttpUploadedFile::fake()->image('image.png', 80, 40),
        ], ['Accept' => 'application/json'])->assertStatus(500);

        $this->assertSame(0, Upload::query()->count());
        $log = UploadLog::query()->firstOrFail();
        $this->assertSame(Errors::ServerError->code(), $log->code);
        $this->assertSame('', $log->url);
        $this->assertSame('文件上传失败', $log->message);
    }

    public static function storageFailures(): array
    {
        return [[false], [true]];
    }

    public function testProcessingFailureDoesNotStoreImage(): void
    {
        config(['pin.upload.thumb.l.width' => 0]);

        $this->post('/_tests/uploads/image', [
            'file' => HttpUploadedFile::fake()->image('image.png', 80, 40),
        ], ['Accept' => 'application/json'])->assertStatus(500);

        $this->assertSame(0, Upload::query()->count());
        $this->assertSame(Errors::ServerError->code(), UploadLog::query()->firstOrFail()->code);
        $this->assertSame([], Storage::disk('upload')->allFiles());
    }

    public function testSupportsNestedFieldAndCustomUploadRule(): void
    {
        $data = $this->post('/_tests/uploads/document', [
            'payload' => ['document' => HttpUploadedFile::fake()->createWithContent(
                'report.txt', 'Document content'
            )],
        ], ['Accept' => 'application/json'])->assertSuccessful()->json();

        $this->assertStringStartsWith('documents/'.now()->format('Ym/d/'), $data['path']);
        $this->assertSame('Document content', Storage::disk('documents')->get($data['path']));
        $this->assertSame('documents', Upload::query()->firstOrFail()->disk);
        $this->assertSame('report.txt', UploadLog::query()->firstOrFail()->original_name);
    }

    public function testRejectsNonImageAndRecordsValidationFailure(): void
    {
        $this->post('/_tests/uploads/image', [
            'file' => HttpUploadedFile::fake()->createWithContent('image.png', 'Not an image')
                ->mimeType('text/plain'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();

        $this->assertSame(0, Upload::query()->count());
        $this->assertSame(
            UploadErrors::UploadExtensionInvalid->code(), UploadLog::query()->firstOrFail()->code
        );
        $this->assertSame([], Storage::disk('upload')->allFiles());
    }

    public function testRejectsOversizedImage(): void
    {
        $this->post('/_tests/uploads/image', [
            'file' => HttpUploadedFile::fake()->image('large.png')->size(5121),
        ], ['Accept' => 'application/json'])->assertUnprocessable();

        $this->assertSame(0, Upload::query()->count());
        $this->assertSame(
            UploadErrors::UploadSizeTooLarge->code(), UploadLog::query()->firstOrFail()->code
        );
    }

    #[DataProvider('invalidFiles')]
    public function testRejectsMissingOrInvalidFile(array $data): void
    {
        $this->postJson('/_tests/uploads/image', $data)
            ->assertUnprocessable()->assertJsonValidationErrors('file', 'data.errors');

        $this->assertSame(0, Upload::query()->count());
        $this->assertSame(0, UploadLog::query()->count());
    }

    public static function invalidFiles(): array
    {
        return [[[]], [['file' => 'invalid']], [['file' => ['invalid']]]];
    }

    public function testDoesNotRecordUploadsTwice(): void
    {
        $this->post('/_tests/uploads/image', [
            'file' => HttpUploadedFile::fake()->image('image.png'),
        ], ['Accept' => 'application/json'])->assertSuccessful();

        new UploadMiddleware()->terminate($this->app['request']);

        $this->assertSame(1, Upload::query()->count());
        $this->assertSame(1, UploadLog::query()->count());
        $this->assertSame([], UploadedFile::items());
    }

    public function testRecordsFilesFromTerminatingRequest(): void
    {
        $request = Request::create('/original');
        $file = new UploadedFile(HttpUploadedFile::fake()->image('original.png'), [], [
            'disk' => 'upload',
        ]);
        $file->storeAs('original');
        $request->attributes->set('uploaded-files', [$file]);
        $this->app->instance('request', Request::create('/another'));

        new UploadMiddleware()->terminate($request);

        $this->assertSame('original.png', UploadLog::query()->firstOrFail()->original_name);
        $this->assertFalse($request->attributes->has('uploaded-files'));
    }

    protected function remoteDisk(): FilesystemAdapter
    {
        $disk = Mockery::mock(FilesystemAdapter::class);
        $disk->shouldReceive('getAdapter')->andReturn(Mockery::mock(FlysystemAdapter::class));
        $disk->shouldReceive('path')->andReturnUsing(
            static fn (string $path): string => '/remote/'.ltrim($path, '/')
        );
        $disk->shouldReceive('url')->andReturnUsing(
            static fn (string $path): string => 'https://files.example.test/'.ltrim($path, '/')
        );

        return $disk;
    }
}

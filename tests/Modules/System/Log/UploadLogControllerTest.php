<?php

declare(strict_types=1);

namespace Tests\Modules\System\Log;

use App\Modules\System\Log\UploadLogController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Errors\Errors;
use Tests\Models\ModelTestCase;

class UploadLogControllerTest extends ModelTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate('2026_03_29_165754_create_uploads_table.php');
        config(['cache.default' => 'array']);
        Mail::fake();

        Route::get('/_tests/upload-logs', [UploadLogController::class, 'index']);
        Route::get('/_tests/upload-logs/options', [UploadLogController::class, 'options']);
    }

    public function testReturnsSortedOptionsWithOriginalValueTypes(): void
    {
        foreach ([
            ['png', 403], ['2', 1000], ['10', 60001], ['png', 0], ['jpg', 403], ['', 0],
        ] as [$extension, $code]) {
            $this->insertLog(['extension' => $extension, 'code' => $code]);
        }

        $data = $this->getJson('/_tests/upload-logs/options')->assertSuccessful()->json('data');

        $this->assertSame([
            ['label' => '', 'value' => ''],
            ['label' => '10', 'value' => '10'],
            ['label' => '2', 'value' => '2'],
            ['label' => 'jpg', 'value' => 'jpg'],
            ['label' => 'png', 'value' => 'png'],
        ], $data['extensions']);
        $this->assertSame([
            ['label' => '0/上传成功', 'value' => 0],
            ['label' => '403/'.Errors::Forbidden->message(), 'value' => 403],
            ['label' => '1000/'.Errors::CreateFailed->message(), 'value' => 1000],
            ['label' => '60001/'.Errors::Unknown->message(), 'value' => 60001],
        ], $data['codes']);
    }

    public function testReturnsEmptyOptions(): void
    {
        $this->getJson('/_tests/upload-logs/options')
            ->assertSuccessful()
            ->assertJsonPath('data', ['extensions' => [], 'codes' => []]);
    }

    public function testReusesOptionsCache(): void
    {
        $this->insertLog();
        $data = $this->getJson('/_tests/upload-logs/options')->assertSuccessful()->json('data');
        $this->assertSame($data, Cache::get('upload_logs.options'));
        DB::enableQueryLog();
        DB::flushQueryLog();

        $this->getJson('/_tests/upload-logs/options')
            ->assertSuccessful()->assertJsonPath('data', $data);

        $this->assertSame([], DB::getQueryLog());
    }

    public function testFiltersLogsAndReturnsDecodedInfo(): void
    {
        $id = $this->insertLog();
        $this->insertLog(['code' => 403]);
        $this->insertLog(['extension' => 'jpg']);
        $this->insertLog(['username' => 'other']);

        $data = $this->getJson('/_tests/upload-logs?'.http_build_query([
            'name' => 'report',
            'original_name' => 'source',
            'path' => 'images',
            'extension' => ['png'],
            'code' => ['0'],
            'username' => 'uploader',
            'ip' => '127.0.0',
            'created_at' => '2026-09-01,2026-09-30',
        ]))->assertSuccessful()->json('data');

        $this->assertSame(1, $data['total']);
        $this->assertSame([$id], array_column($data['items'], 'id'));
        $this->assertSame(['errors' => []], $data['items'][0]['info']);
    }

    public function testIgnoresUnsupportedRequestIdFilter(): void
    {
        $id = $this->insertLog();
        DB::enableQueryLog();
        DB::flushQueryLog();

        $data = $this->getJson('/_tests/upload-logs?request_id=unknown')
            ->assertSuccessful()->json('data');

        $this->assertSame([$id], array_column($data['items'], 'id'));
        foreach (DB::getQueryLog() as $query) {
            $this->assertStringNotContainsString('request_id', $query['query']);
        }
    }

    #[DataProvider('invalidFilters')]
    public function testRejectsInvalidFilters(array $query, string $field): void
    {
        $this->getJson('/_tests/upload-logs?'.http_build_query($query))
            ->assertUnprocessable()->assertJsonValidationErrors($field, 'data.errors');
    }

    public static function invalidFilters(): array
    {
        return [
            [['extension' => 'png'], 'extension'],
            [['extension' => [['png']]], 'extension.0'],
            [['code' => '0'], 'code'],
            [['code' => [['0']]], 'code.0'],
            [['code' => ['invalid']], 'code.0'],
            [['code' => ['1.5']], 'code.0'],
        ];
    }

    protected function insertLog(array $attributes = []): int
    {
        return DB::table('upload_logs')->insertGetId(array_replace([
            'file_id' => '00000000-0000-0000-0000-000000000001',
            'uid' => 5,
            'username' => 'uploader',
            'user_type' => 'admin',
            'disk' => 'upload',
            'name' => 'report.png',
            'original_name' => 'source.png',
            'path' => 'images/report.png',
            'extension' => 'png',
            'mime_type' => 'image/png',
            'size' => 1024,
            'code' => 0,
            'ip' => '127.0.0.1',
            'info' => json_encode(['errors' => []]),
            'created_at' => '2026-09-21 10:00:00',
            'updated_at' => '2026-09-21 10:00:00',
        ], $attributes));
    }
}

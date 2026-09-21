<?php

declare(strict_types=1);

use App\Models\Upload;
use App\Models\UploadLog;
use App\Routes\UploadRoute;
use Illuminate\Http\UploadedFile;

it('uploads image successfully', function () {
    $filename = uniqid('image_', true).'.jpg';

    $url = UploadRoute::Image->testJson($this, [
        'file' => UploadedFile::fake()->image($filename, 120, 120),
    ])
        ->assertSuccessful()
        ->assertJsonStructure(['data' => ['url']])
        ->json('data.url');

    expect($url)->toBeString()->toContain('/testing-uploads/');

    $upload = Upload::findBy('original_name', $filename);
    $uploadLog = UploadLog::findBy('original_name', $filename);

    expect($upload)->not()->toBeNull()
        ->and($upload->url)->toBe($url)
        ->and($upload->width)->toBe(120)
        ->and($upload->height)->toBe(120)
        ->and($uploadLog)->not()->toBeNull()
        ->and($uploadLog->code)->toBe(0)
        ->and($uploadLog->message)->toBe('上传成功');
});

it('requires image file', function () {
    UploadRoute::Image->testJson($this)
        ->assertCode(422, 422)
        ->assertInvalid('file');
});

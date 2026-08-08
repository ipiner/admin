<?php

declare(strict_types=1);

use App\Models\UploadLog;
use App\Routes\System\AdminRoute;
use App\Routes\System\LogRoute;
use Database\Factories\System\AdminFactory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;

it(lists('upload logs and options'), function () {
    Cache::forget(new UploadLog()->getTable().'.options');
    $admin = AdminFactory::testingAdmin();
    $filename = uniqid().'.png';
    AdminRoute::UpdateAvatar->testing($this)
        ->withRouteParams(['id' => $admin->id])
        ->withPayload([
            'file' => UploadedFile::fake()->image($filename),
        ])
        ->updated();

    LogRoute::UploadLog->testJson($this, ['original_name' => $filename])
        ->assertPaginated();

    // options
    LogRoute::UploadLogOption->testJson($this)
        ->assertSuccessful()
        ->assertJsonStructure([
            'data' => [
                'extensions' => [
                    '*' => ['label', 'value'],
                ],
                'codes' => [
                    '*' => ['label', 'value'],
                ],
            ],
        ]);
});

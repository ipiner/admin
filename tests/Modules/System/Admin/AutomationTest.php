<?php

declare(strict_types=1);

use App\Routes\System\AdminRoute;
use Pin\Route\Testing\TestingTask;

it(runsTestsAutomatically('admin'), function () {
    AdminRoute::tests($this)->tasks()->each(function (TestingTask $task) {
        // 更新管理员上传头像单独测试
        if ($task->testing->route === AdminRoute::UpdateAvatar) {
            $task->testing->withPayload([]);
        }

        $task->run();
    });
});

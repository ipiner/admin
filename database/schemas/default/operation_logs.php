<?php

return [
    'label' => '操作日志',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['operation_logs'],
        // 自定义
    ],
];

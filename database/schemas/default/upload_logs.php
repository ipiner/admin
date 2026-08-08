<?php

return [
    'label' => '文件上传日志',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['upload_logs'],
        // 自定义
    ],
];

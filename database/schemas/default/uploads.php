<?php

return [
    'label' => '上传文件',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['uploads'],
        // 自定义
    ],
];

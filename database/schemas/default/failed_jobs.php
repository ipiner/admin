<?php

return [
    'label' => 'Failed Job',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['failed_jobs'],
        // 自定义
    ],
];

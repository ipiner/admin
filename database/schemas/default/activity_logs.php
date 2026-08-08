<?php

return [
    'label' => '行为日志',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['activity_logs'],
        // 自定义
    ],
];

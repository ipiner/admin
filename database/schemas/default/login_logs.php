<?php

return [
    'label' => '登录日志',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['login_logs'],
        // 自定义
    ],
];

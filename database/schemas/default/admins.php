<?php

return [
    'label' => '管理员',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['admins'],
        // 自定义
    ],
];

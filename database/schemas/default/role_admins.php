<?php

return [
    'label' => '角色管理员关系',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['role_admins'],
        // 自定义
    ],
];

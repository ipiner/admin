<?php

return [
    'label' => '菜单权限关系',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['role_menus'],
        // 自定义
    ],
];

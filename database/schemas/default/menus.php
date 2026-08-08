<?php

return [
    'label' => '菜单',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['menus'],
        // 自定义
    ],
];

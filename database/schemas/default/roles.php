<?php

return [
    'label' => '角色',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['roles'],
        // 自定义
    ],
];

<?php

return [
    'label' => '文章',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['articles'],
        // 自定义扩展字段（不会被覆盖）
    ],
];

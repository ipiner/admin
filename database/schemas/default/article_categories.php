<?php

return [
    'label' => '文章分类',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['article_categories'],
        // 自定义扩展字段（不会被覆盖）
    ],
];

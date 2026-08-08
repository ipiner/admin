<?php

return [
    'label' => '上传分类',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['upload_categories'],
        // 自定义
    ],
];

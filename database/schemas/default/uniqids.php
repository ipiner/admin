<?php

return [
    'label' => '唯一id生成器数持久化',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['uniqids'],
        // 自定义
    ],
];

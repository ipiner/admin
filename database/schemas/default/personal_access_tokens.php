<?php

return [
    'label' => 'Personal Access Token',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['personal_access_tokens'],
        // 自定义
    ],
];

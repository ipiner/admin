<?php

return [
    'label' => 'Password Reset',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['password_resets'],
        // 自定义
    ],
];

<?php

return [
    'label' => 'Migration',
    'attributes' => [
        ...(require __DIR__.'/__attributes__.php')['migrations'],
        // 自定义
    ],
];

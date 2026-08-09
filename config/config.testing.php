<?php

return [
    'database' => [
        'default' => 'testing',
        'redis' => [
            'default' => ['database' => 10],
            'cache' => ['database' => 10],
        ],
    ],
    'filesystems' => [
        'disks' => [
            'upload' => [
                'root' => public_path('testing-uploads'),
                'url' => env('APP_URL').'/testing-uploads',
                'throw' => true,
            ],
        ],
    ],
];

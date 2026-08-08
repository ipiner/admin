<?php

use App\Models\System\Menu;

return [
    'except' => [
        'api/account/*',
    ],
    'menu_model' => Menu::class,
];

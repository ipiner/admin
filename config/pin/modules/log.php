<?php

use Pin\Modules\Log\Events\LogEvent;

return [
    LogEvent::Operation->value => [
        'subject_name_columns' => [
            'admins' => 'username',
            'users' => 'username',
            'menus' => 'name',
            'roles' => 'name',
            'uploads' => 'name',
            'articles' => 'title',
        ],
    ],
];

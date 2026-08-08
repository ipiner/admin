<?php

use Laravel\Sanctum\Http\Middleware\AuthenticateSession;
use Pin\Http\Middleware\EncryptCookies;
use Pin\Http\Middleware\ValidateCsrfToken;

return [
    'stateful_always' => [
        'api/csrf',
    ],
    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],
];

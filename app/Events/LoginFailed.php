<?php

namespace App\Events;

use App\Models\System\Admin;

class LoginFailed
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        public Admin $admin,
        public int $code,
        public string $message,
        public int $internalCode,
        public array $context = []
    ) {
        //
    }
}

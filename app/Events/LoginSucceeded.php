<?php

namespace App\Events;

use App\Models\System\Admin;

class LoginSucceeded
{
    /**
     * Create a new event instance.
     */
    public function __construct(public Admin $admin)
    {
    }
}

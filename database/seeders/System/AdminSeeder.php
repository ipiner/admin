<?php

declare(strict_types=1);

namespace Database\Seeders\System;

use App\Models\System\Admin;
use Illuminate\Database\Seeder;
use Pin\Support\Facades\Password;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Admin::create([
            'id' => Admin::ADMINISTRATOR_ID,
            'username' => 'admin',
            'realname' => '系统管理员',
            'password' => Password::encode('test@123'),
        ]);
    }
}

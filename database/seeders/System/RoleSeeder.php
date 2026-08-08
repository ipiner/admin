<?php

declare(strict_types=1);

namespace Database\Seeders\System;

use App\Models\System\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'id' => Role::SUPER_ROLE_ID,
            'name' => '超级管理员',
            'remark' => '',
        ]);
    }
}

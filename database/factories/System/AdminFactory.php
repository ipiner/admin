<?php

declare(strict_types=1);

namespace Database\Factories\System;

use App\Models\System\Admin;
use App\Modules\System\Admin\Actions\CreateAdminAction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Pin\Support\Facades\Password;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected $model = Admin::class;

    public function definition(): array
    {
        return CreateAdminAction::fake([
            'password' => Password::encode('test@123'),
            'captcha_rule' => Arr::random(['', 'rev', 'normal']),
        ]);
    }

    public static function superAdmin(): Admin
    {
        return Cache::lock('admins.superAdmin.create', 60)->block(5, function () {
            if ($item = Admin::find(Admin::ADMINISTRATOR_ID)) {
                return $item;
            }

            return static::new()->create([
                'id' => Admin::ADMINISTRATOR_ID,
                'username' => Str::random(),
            ]);
        });
    }

    public static function testingAdmin(): Admin
    {
        return Cache::lock('admins.testing.create', 60)->block(5, function () {
            if ($item = Admin::findBy('username', 'testing')) {
                return $item;
            }

            return static::new()->create(['username' => 'testing']);
        });
    }
}

<?php

declare(strict_types=1);

namespace Database\Factories\System;

use App\Models\System\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

/**
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => Str::random(),
            'remark' => Str::random(),
        ];
    }

    public static function testingRole(): Role
    {
        return Cache::lock('roles.testing.create', 60)->block(5, function () {
            if ($item = Role::findBy('name', 'testing')) {
                return $item;
            }

            return RoleFactory::new()->create(['name' => 'testing']);
        });
    }
}

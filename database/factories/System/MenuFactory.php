<?php

declare(strict_types=1);

namespace Database\Factories\System;

use App\Models\System\Menu;
use App\Modules\System\Menu\Actions\CreateMenuAction;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Cache;

/**
 * @extends Factory<Menu>
 */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return CreateMenuAction::fake();
    }

    public static function testingMenu(): Menu
    {
        return Cache::lock('menus.testing.create', 60)->block(5, function () {
            if ($item = Menu::findBy('code', 'testing')) {
                return $item;
            }

            return static::new()->create(['code' => 'testing']);
        });
    }
}

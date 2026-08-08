<?php

declare(strict_types=1);

namespace Database\Seeders\System;

use App\Models\System\Menu;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Pin\Route\Routable;

class MenuSeeder extends Seeder
{
    use Concerns\HasContentMenus,
        Concerns\HasSystemMenus;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->createMenus($this->menus(), []);
    }

    protected function create(array $data, array $pids): void
    {
        $route = $data['route'] ?? '';
        if ($route instanceof Routable) {
            $data = [
                'name' => $route->title(),
                'code' => $route->name(),
                'type' => $route->method() === 'GET' ? Menu::MENU : Menu::BUTTON,
                ...$data,
                'route' => str_replace('/api', '', $route->uri()),
            ];
        }

        $id = Menu::where('code', $data['code'])->first()?->id
            ?? new Menu()->newUniqueId();

        $data['id'] = $id;
        $data['pid'] = count($pids) > 0 ? end($pids) : 0;
        $pids[] = $id; // 父父id/父id/id
        $data['path'] = implode('/', $pids);

        $children = Arr::pull($data, 'children');
        Menu::create($data);

        if ($children) {
            $this->createMenus($children, $pids);
        }
    }

    /**
     * 生成菜单
     */
    protected function createMenus(array $data, array $pids): void
    {
        foreach ($data as $item) {
            $this->create(
                $item instanceof Routable ? ['route' => $item] : $item,
                $pids,
            );
        }
    }

    /**
     * @return array[]
     */
    protected function menus(): array
    {
        return [
            $this->content(),
            $this->system(),
        ];
    }
}

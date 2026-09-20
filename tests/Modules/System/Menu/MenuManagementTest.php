<?php

declare(strict_types=1);

namespace Tests\Modules\System\Menu;

use App\Models\System\Menu;
use App\Modules\System\Menu\Actions\CreateMenuAction;
use App\Modules\System\Menu\MenuController;
use App\Routes\AccountRoute;
use App\Routes\Auth\LoginRoute;
use App\Routes\System\LogRoute;
use App\Routes\System\MenuRoute;
use App\Routes\System\RoleRoute;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Mockery;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Errors\Errors;
use Pin\Models\Cache\Cacher;
use Tests\Models\ModelTestCase;

class MenuManagementTest extends ModelTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate('0000_create_menus_table.php');
        config(['cache.default' => 'array']);
        Cache::store('array')->put(Menu::class.'operation-log-disabled', true);
        Mail::fake();

        $generator = Mockery::mock();
        $generator->shouldReceive('generate')->andReturn(1000);
        $this->app->instance('pin.id.redis', $generator);

        foreach ([10, 20, 30, 40, 50] as $id) {
            DB::table('menus')->insert([
                'id' => $id,
                'pid' => $id === 30 ? 20 : 0,
                'path' => $id === 30 ? '20/30' : (string) $id,
                'level' => $id === 30 ? 2 : 1,
                'name' => $id === 10 ? '自定义菜单名称' : 'Menu '.$id,
                'code' => $id === 10 ? MenuRoute::Index->name() : 'menu-'.$id,
                'sort' => $id,
                'route' => '/menu-'.$id,
                'enabled' => $id === 50 ? Menu::SYSTEM_ENABLED : Menu::ENABLED,
            ]);
        }

        $cacher = $this->createStub(Cacher::class);
        $cacher->method('get')->willReturnCallback(static fn (int $id) => Menu::query()->find($id));
        $cacher->method('getAll')->willReturnCallback(
            static fn () => Menu::query()->get()->keyBy('id')
        );
        $cacher->method('forget')->willReturn(true);
        $cachers = $this->cacheProperty->getValue();
        $cachers[Menu::class] = $cacher;
        $this->cacheProperty->setValue(null, $cachers);

        Route::get('/_tests/menus', [MenuController::class, 'index']);
        Route::post('/_tests/menus', [MenuController::class, 'create']);
        Route::put('/_tests/menus/{id}', [MenuController::class, 'update']);
        Route::delete('/_tests/menus/{id}', [MenuController::class, 'delete']);
        Route::put('/_tests/menus/{id}/enabled', [MenuController::class, 'updateEnabled']);
        Route::put('/_tests/menus/{id}/visible', [MenuController::class, 'updateVisible']);
        Route::get('/_tests/menus/selector', [MenuController::class, 'selector']);
        Route::get('/_tests/menus/codes', [MenuController::class, 'availableCodes']);
    }

    public function testAvailableCodesUseSavedMenuNames(): void
    {
        $data = $this->getJson('/_tests/menus/codes?all=1')->assertSuccessful()->json('data');
        $codes = array_column($data, 'value');
        $options = array_column($data, null, 'value');

        $this->assertSame([
            'label' => '自定义菜单名称',
            'value' => 'system.menus',
            'name' => 'SYSTEM_MENUS',
        ], $options[MenuRoute::Index->name()]);
        $sorted = $codes;
        sort($sorted);
        $this->assertSame($sorted, $codes);

        foreach ([
            AccountRoute::UpdatePassword,
            LoginRoute::Login,
            MenuRoute::AvailableCodes,
            MenuRoute::Selector,
            RoleRoute::Selector,
            LogRoute::UploadLogOption,
        ] as $route) {
            $this->assertNotContains($route->name(), $codes);
        }

        $unused = $this->getJson('/_tests/menus/codes')->assertSuccessful()->json('data');
        $this->assertNotContains(MenuRoute::Index->name(), array_column($unused, 'value'));
        $this->assertContains(MenuRoute::Delete->name(), array_column($unused, 'value'));
    }

    public function testFakePreservesProvidedAttributes(): void
    {
        $attributes = ['name' => 'Provided', 'icon' => 'custom-icon', 'pid' => 20];
        $data = CreateMenuAction::fake($attributes);

        foreach ($attributes as $field => $value) {
            $this->assertSame($value, $data[$field]);
        }
        $this->assertSame('', CreateMenuAction::fake()['icon']);
    }

    #[DataProvider('statusFields')]
    public function testUpdatesOnlyRequestedStatus(string $field): void
    {
        $menu = Menu::query()->findOrFail(40);

        $this->putJson('/_tests/menus/40/'.$field, [
            $field => '0',
            'v' => $menu->v,
            'name' => 'Ignored',
            'pid' => 20,
        ])->assertSuccessful()->assertJsonPath('data.updated', true);

        $menu->refresh();
        $this->assertSame(0, $menu->$field);
        $this->assertSame('Menu 40', $menu->name);
        $this->assertSame(0, $menu->pid);
    }

    #[DataProvider('statusFields')]
    public function testRequiresStatusField(string $field): void
    {
        $menu = Menu::query()->findOrFail(40);
        $version = $menu->v;

        $this->putJson('/_tests/menus/40/'.$field, ['v' => $version])
            ->assertUnprocessable()->assertJsonValidationErrors($field, 'data.errors');

        $this->assertSame($version, $menu->refresh()->v);
    }

    public static function statusFields(): array
    {
        return [['enabled'], ['visible']];
    }

    #[DataProvider('protectedStatuses')]
    public function testProtectsEnabledStatus(int $id, string $enabled, ?string $message): void
    {
        $menu = Menu::query()->findOrFail($id);
        $original = $menu->enabled;
        $response = $this->putJson('/_tests/menus/'.$id.'/enabled', [
            'enabled' => $enabled,
            'v' => $menu->v,
        ]);

        if ($message) {
            $response->assertJsonPath('code', Errors::UpdateFailed->code())
                ->assertJsonPath('message', $message);
            $this->assertSame($original, $menu->refresh()->enabled);

            return;
        }

        $response->assertSuccessful();
        $this->assertSame((int) $enabled, $menu->refresh()->enabled);
    }

    public static function protectedStatuses(): array
    {
        return [
            [10, '0', '[自定义菜单名称] 不可禁用'],
            [10, '1', null],
            [50, '0', '禁止修改 [Menu 50] 启用状态'],
            [50, '1', '禁止修改 [Menu 50] 启用状态'],
            [50, '2', null],
        ];
    }

    public function testCreatesMenuWithPathAndDefaultSort(): void
    {
        $id = $this->postJson('/_tests/menus', $this->payload(['pid' => 20]))
            ->assertSuccessful()->json('data.id');

        $menu = Menu::query()->findOrFail($id);
        $this->assertSame('20/'.$id, $menu->path);
        $this->assertSame(2, $menu->level);
        $this->assertSame($id, $menu->sort);
    }

    public function testValidatesSiblingNamesFromFormPayload(): void
    {
        $this->post('/_tests/menus', $this->payload(['pid' => 20, 'name' => 'Menu 30']), [
            'Accept' => 'application/json',
        ])->assertUnprocessable()->assertJsonValidationErrors('name', 'data.errors');

        $this->assertSame(5, Menu::query()->count());
    }

    public function testAllowsSameNameUnderDifferentParents(): void
    {
        $this->post('/_tests/menus', $this->payload(['pid' => 20, 'name' => 'Menu 40']), [
            'Accept' => 'application/json',
        ])->assertSuccessful();

        $this->assertSame(6, Menu::query()->count());
    }

    public function testMovesMenuWithDescendants(): void
    {
        $menu = Menu::query()->findOrFail(20);

        $this->putJson('/_tests/menus/20', $this->payload([
            'pid' => 40,
            'code' => $menu->code,
            'name' => $menu->name,
            'route' => $menu->route,
            'enabled' => '1',
            'v' => $menu->v,
        ]))->assertSuccessful();

        $child = Menu::query()->findOrFail(30);
        $this->assertSame('40/20', $menu->refresh()->path);
        $this->assertSame(2, $menu->level);
        $this->assertSame('40/20/30', $child->path);
        $this->assertSame(3, $child->level);
    }

    public function testRejectsCircularParent(): void
    {
        $menu = Menu::query()->findOrFail(20);

        $this->putJson('/_tests/menus/20', $this->payload([
            'pid' => 30,
            'v' => $menu->v,
        ]))->assertUnprocessable()->assertJsonValidationErrors('pid', 'data.errors');

        $this->assertSame('20', $menu->refresh()->path);
    }

    public function testRejectsStaleVersion(): void
    {
        $menu = Menu::query()->findOrFail(40);

        $this->putJson('/_tests/menus/40/enabled', [
            'enabled' => '0',
            'v' => $menu->v + 1,
        ])->assertJsonPath('code', Errors::DataVersionMismatch->code());

        $this->assertSame(1, $menu->refresh()->enabled);
    }

    #[DataProvider('deletions')]
    public function testDeletesOnlyAllowedLeafMenus(int $id, ?string $message): void
    {
        $response = $this->deleteJson('/_tests/menus/'.$id);
        if ($message) {
            $response->assertJsonPath('code', Errors::DeleteFailed->code())
                ->assertJsonPath('message', $message);
            $this->assertNotNull(Menu::query()->find($id));

            return;
        }

        $response->assertSuccessful()->assertJsonPath('data.deleted', true);
        $this->assertNull(Menu::query()->find($id));
    }

    public static function deletions(): array
    {
        return [
            [10, '禁止删除 [自定义菜单名称]'],
            [20, '请先删除该菜单下的子菜单'],
            [30, null],
        ];
    }

    public function testListsCompleteTreeAndFilteredPages(): void
    {
        $this->getJson('/_tests/menus?page_size=1&q=Menu%2030')
            ->assertSuccessful()
            ->assertJsonCount(5, 'data.items')
            ->assertJsonPath('data.total_page', 1);

        $this->getJson('/_tests/menus?paging=1&q=Menu%2030')
            ->assertSuccessful()
            ->assertJsonCount(1, 'data.items')
            ->assertJsonPath('data.items.0.id', 30);

        $this->getJson('/_tests/menus')
            ->assertSuccessful()->assertJsonCount(5, 'data.items');

        $options = $this->getJson('/_tests/menus/selector')
            ->assertSuccessful()->json('data');
        $this->assertSame([
            'label' => 'Menu 30', 'value' => 30, 'type' => 'menu', 'pid' => 20,
        ], array_column($options, null, 'value')[30]);
    }

    protected function payload(array $data = []): array
    {
        return array_replace([
            'name' => 'Created menu',
            'code' => 'created.menu',
            'type' => Menu::MENU,
            'pid' => 0,
            'sort' => -1,
            'route' => '/created-menu',
            'enabled' => 1,
            'visible' => 1,
        ], $data);
    }
}

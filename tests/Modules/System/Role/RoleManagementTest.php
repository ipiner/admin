<?php

declare(strict_types=1);

namespace Tests\Modules\System\Role;

use App\Models\System\Admin;
use App\Models\System\Menu;
use App\Models\System\Role;
use App\Modules\System\Role\RoleController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Access\AccessProvider;
use Pin\Errors\Errors;
use Pin\Models\Cache\Cacher;
use Pin\Support\Facades\RuntimeCache;
use Tests\Models\ModelTestCase;

class RoleManagementTest extends ModelTestCase
{
    protected Role $role;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate(
            '2026_01_21_213711_create_admins_table.php',
            '2026_01_22_091023_create_roles_table.php',
            '0000_create_menus_table.php',
        );
        config(['cache.default' => 'array', 'pin.access.cache_ttl' => 86400]);
        Cache::store('array')->put(Role::class.'operation-log-disabled', true);
        Mail::fake();
        $this->actingAs(new Admin(['id' => Admin::ADMINISTRATOR_ID]));

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'super'],
            ['id' => 2, 'name' => 'editor'],
            ['id' => 3, 'name' => 'viewer'],
        ]);
        foreach ([10, 20, 30] as $id) {
            DB::table('menus')->insert([
                'id' => $id,
                'pid' => $id === 30 ? 20 : 0,
                'path' => $id === 30 ? '20/30' : (string) $id,
                'level' => $id === 30 ? 2 : 1,
                'name' => 'Menu '.$id,
                'code' => 'menu-'.$id,
                'sort' => $id,
                'route' => '/menu-'.$id,
            ]);
        }
        DB::table('role_menus')->insert([
            ['role_id' => 2, 'menu_id' => 10],
            ['role_id' => 3, 'menu_id' => 10],
        ]);
        DB::table('role_admins')->insert([
            ['role_id' => 2, 'uid' => 2],
            ['role_id' => 2, 'uid' => 3],
            ['role_id' => 3, 'uid' => 4],
        ]);

        $this->role = Role::query()->findOrFail(2);
        $cachers = $this->cacheProperty->getValue();
        foreach ([Role::class, Menu::class] as $class) {
            $cacher = $this->createStub(Cacher::class);
            $cacher->method('get')->willReturnCallback(
                fn (int $id) => $class === Role::class && $id === $this->role->id
                    ? $this->role
                    : $class::query()->find($id)
            );
            $cacher->method('getAll')->willReturnCallback(
                static fn () => $class::query()->get()->keyBy('id')
            );
            $cacher->method('forget')->willReturn(true);
            $cachers[$class] = $cacher;
        }
        $this->cacheProperty->setValue(null, $cachers);

        Route::get('/_tests/roles', [RoleController::class, 'index']);
        Route::post('/_tests/roles', [RoleController::class, 'create']);
        Route::put('/_tests/roles/{id}', [RoleController::class, 'update']);
        Route::delete('/_tests/roles/{id}', [RoleController::class, 'delete']);
        Route::get('/_tests/roles/selector', [RoleController::class, 'selector']);
    }

    #[DataProvider('menuAssignments')]
    public function testCreatesRoleWithDistinctMenus(array $menus, array $expected): void
    {
        $id = $this->postJson('/_tests/roles', [
            'name' => 'created', 'menus' => $menus,
        ])->assertSuccessful()->json('data.id');

        $role = Role::query()->findOrFail($id);
        $this->assertSame(
            $expected, $role->menus()->orderBy('menu_id')->pluck('menus.id')->all()
        );
    }

    public static function menuAssignments(): array
    {
        return [[[], []], [[10, 20], [10, 20]], [[10, '10', 20], [10, 20]]];
    }

    #[DataProvider('invalidFields')]
    public function testRejectsInvalidFields(string $method, array $data, string $field): void
    {
        $url = '/_tests/roles'.($method === 'PUT' ? '/2' : '');

        $this->json($method, $url, $this->payload([
            'name' => $method === 'PUT' ? 'editor' : 'created',
            ...$data,
        ]))->assertUnprocessable()->assertJsonValidationErrors($field, 'data.errors');

        $this->assertSame(3, Role::query()->count());
        $this->assertSame([10], $this->role->menus()->pluck('menus.id')->all());
    }

    public static function invalidFields(): array
    {
        $cases = [];
        foreach (['POST', 'PUT'] as $method) {
            $cases[] = [$method, ['name' => ['invalid']], 'name'];
            $cases[] = [$method, ['remark' => ['invalid']], 'remark'];
            $cases[] = [$method, ['menus' => '10'], 'menus'];
            $cases[] = [$method, ['menus' => [[10]]], 'menus.0'];
        }

        return $cases;
    }

    public function testRejectsMissingAndDeletedMenus(): void
    {
        DB::table('menus')->where('id', 30)->update(['deleted_at' => time()]);

        $this->putJson('/_tests/roles/2', $this->payload(['menus' => [30, -1]]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('menus', 'data.errors')
            ->assertJsonPath('message', '菜单 [30,-1] 不存在');

        $this->assertSame([10], $this->role->menus()->pluck('menus.id')->all());
    }

    public function testSyncsMenusAndRefreshesMemberPermissions(): void
    {
        $this->role->load('menus');
        $this->warmPermissions();
        $unrelated = Cache::get('auth-access:4');

        $this->putJson('/_tests/roles/2', $this->payload(['menus' => [30, 20, 30]]))
            ->assertSuccessful()->assertJsonPath('data.updated', true);

        $this->assertSame([20, 30], $this->role->menus->pluck('id')->sort()->values()->all());
        foreach ([2, 3] as $id) {
            $this->assertNull(Cache::get('auth-access:'.$id));
            $this->assertNull(RuntimeCache::get('auth-access:'.$id));
            $this->assertSame(['menu-20', 'menu-30'], $this->permissions($id));
        }
        $this->assertSame($unrelated, Cache::get('auth-access:4'));
        $this->assertSame($unrelated, RuntimeCache::get('auth-access:4'));
    }

    public function testKeepsPermissionCacheWhenMenusAreUnchanged(): void
    {
        $this->warmPermissions();
        $cached = Cache::get('auth-access:2');

        $this->putJson('/_tests/roles/2', $this->payload(['remark' => 'Changed']))
            ->assertSuccessful();

        $this->assertSame($cached, Cache::get('auth-access:2'));
        $this->assertSame($cached, RuntimeCache::get('auth-access:2'));
    }

    #[DataProvider('emptyMenus')]
    public function testClearsMenus(array $data): void
    {
        $payload = $this->payload();
        unset($payload['menus']);

        $this->putJson('/_tests/roles/2', [...$payload, ...$data])->assertSuccessful();

        $this->assertSame([], $this->role->menus()->pluck('menus.id')->all());
    }

    public static function emptyMenus(): array
    {
        return [[[]], [['menus' => null]], [['menus' => []]]];
    }

    public function testKeepsSuperRoleMenusUnchanged(): void
    {
        $role = Role::query()->findOrFail(1);

        $this->putJson('/_tests/roles/1', $this->payload([
            'name' => $role->name, 'menus' => [20], 'v' => $role->v,
        ]))->assertSuccessful();

        $this->assertSame([], $role->menus()->pluck('menus.id')->all());
    }

    public function testForbidsUpdatingSuperRoleByOrdinaryAdmin(): void
    {
        $this->actingAs(new Admin(['id' => 2]));
        $role = Role::query()->findOrFail(1);

        $this->putJson('/_tests/roles/1', $this->payload([
            'name' => 'Changed', 'v' => $role->v,
        ]))->assertForbidden()->assertJsonPath('message', '禁止修改该角色');

        $this->assertSame('super', $role->refresh()->name);
    }

    public function testForbidsDeletingSuperRole(): void
    {
        $this->deleteJson('/_tests/roles/1')
            ->assertForbidden()->assertJsonPath('message', '禁止删除该角色');

        $this->assertNotNull(Role::query()->find(1));
    }

    public function testDeletingRoleRevokesMemberPermissions(): void
    {
        $this->warmPermissions();
        $unrelated = Cache::get('auth-access:4');

        $this->deleteJson('/_tests/roles/2')
            ->assertSuccessful()->assertJsonPath('data.deleted', true);

        $this->assertNull(Role::query()->find(2));
        foreach ([2, 3] as $id) {
            $this->assertNull(Cache::get('auth-access:'.$id));
            $this->assertNull(RuntimeCache::get('auth-access:'.$id));
            $this->assertSame([], $this->permissions($id));
        }
        $this->assertSame($unrelated, Cache::get('auth-access:4'));
    }

    public function testRejectsDuplicateName(): void
    {
        $this->putJson('/_tests/roles/2', $this->payload(['name' => 'viewer']))
            ->assertUnprocessable()->assertJsonValidationErrors('name', 'data.errors');

        $this->assertSame('editor', $this->role->refresh()->name);
    }

    public function testRejectsStaleVersion(): void
    {
        $this->putJson('/_tests/roles/2', $this->payload([
            'v' => $this->role->v + 1, 'menus' => [20],
        ]))->assertJsonPath('code', Errors::DataVersionMismatch->code());

        $this->assertSame([10], $this->role->menus()->pluck('menus.id')->all());
    }

    public function testListsRolesWithMenuPathsAndSelectorOptions(): void
    {
        $this->role->menus()->sync([30]);

        $items = $this->getJson('/_tests/roles')->assertSuccessful()->json('data.items');

        $this->assertTrue($items[0]['super']);
        $this->assertSame([], $items[0]['menus']);
        $this->assertFalse($items[1]['super']);
        $this->assertSame([
            ['id' => 30, 'name' => 'Menu 30', 'paths' => [20, 30]],
        ], $items[1]['menus']);
        $this->assertArrayNotHasKey('deleted_at', $items[1]);

        $this->getJson('/_tests/roles/selector')->assertSuccessful()->assertJsonPath('data', [
            ['label' => 'super', 'value' => 1],
            ['label' => 'editor', 'value' => 2],
            ['label' => 'viewer', 'value' => 3],
        ]);
    }

    protected function payload(array $data = []): array
    {
        return array_replace([
            'name' => 'editor', 'menus' => [10], 'v' => $this->role->v,
        ], $data);
    }

    protected function permissions(int $id): array
    {
        return new AccessProvider(new Admin(['id' => $id]))->codes();
    }

    protected function warmPermissions(): void
    {
        foreach ([2, 3, 4] as $id) {
            $this->assertSame(['menu-10'], $this->permissions($id));
        }
    }
}

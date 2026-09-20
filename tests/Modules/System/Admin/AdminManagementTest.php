<?php

declare(strict_types=1);

namespace Tests\Modules\System\Admin;

use App\Models\System\Admin;
use App\Models\System\Role;
use App\Modules\System\Admin\AdminController;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Errors\Errors;
use Pin\Models\Cache\Cacher;
use Pin\Password\Middleware\DecodePassword;
use Pin\Support\Facades\Password;
use Pin\Support\Facades\RuntimeCache;
use Tests\Models\ModelTestCase;

class AdminManagementTest extends ModelTestCase
{
    protected Admin $admin;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate(
            '2026_01_21_213711_create_admins_table.php',
            '2026_01_22_091023_create_roles_table.php',
        );
        config(['cache.default' => 'array']);
        Cache::store('array')->put(Admin::class.'operation-log-disabled', true);
        Mail::fake();
        Storage::fake('upload');

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'super'],
            ['id' => 2, 'name' => 'editor'],
            ['id' => 3, 'name' => 'reviewer'],
        ]);
        $operator = Admin::create([
            'id' => 1,
            'username' => 'operator',
            'realname' => 'Operator',
            'password' => Password::encode('operator-password'),
        ]);
        $this->admin = Admin::create([
            'id' => 2,
            'username' => 'admin-user',
            'realname' => 'Original',
            'avatar' => 'https://example.test/original.png',
            'password' => Password::encode('original-password'),
        ]);
        $this->admin->refresh();
        $this->admin->roles()->attach(2);
        $this->actingAs($operator);

        $cachers = $this->cacheProperty->getValue();
        foreach ([Admin::class, Role::class] as $class) {
            $cacher = $this->createStub(Cacher::class);
            $cacher->method('get')->willReturnCallback(
                fn (int $id) => $class === Admin::class && $id === $this->admin->id
                    ? $this->admin
                    : $class::query()->find($id)
            );
            $cacher->method('getAll')->willReturnCallback(
                static fn () => $class::query()->get()->keyBy('id')
            );
            $cacher->method('forget')->willReturn(true);
            $cachers[$class] = $cacher;
        }
        $this->cacheProperty->setValue(null, $cachers);

        Route::get('/_tests/admins', [AdminController::class, 'index']);
        Route::post('/_tests/admins', [AdminController::class, 'create'])
            ->middleware(DecodePassword::class);
        Route::put('/_tests/admins/{id}', [AdminController::class, 'update'])
            ->middleware(DecodePassword::class);
        Route::delete('/_tests/admins/{id}', [AdminController::class, 'delete']);
        Route::post('/_tests/admins/{id}/avatar', [AdminController::class, 'updateAvatar']);
    }

    #[DataProvider('roleAssignments')]
    public function testCreatesAdminWithDistinctRoles(array $roles, array $expected): void
    {
        $id = $this->postJson('/_tests/admins', $this->payload([
            'username' => 'created-admin',
            'password' => Password::encodeToRequest('created-password'),
            'roles' => $roles,
        ]))->assertSuccessful()->json('data.id');

        $admin = Admin::query()->findOrFail($id);
        $this->assertSame($expected, $admin->roles()->orderBy('role_id')->pluck('roles.id')->all());
        $this->assertTrue(Password::check(
            Password::encode('created-password'), $admin->salt, $admin->password
        ));
    }

    public static function roleAssignments(): array
    {
        return [[[], []], [[2, '2', 3], [2, 3]], [[2, 1, 3], [1]]];
    }

    #[DataProvider('invalidFields')]
    public function testRejectsInvalidFields(string $method, string $field, mixed $value): void
    {
        $url = '/_tests/admins'.($method === 'PUT' ? '/2' : '');
        $error = $field === 'roles' ? 'roles.0' : $field;

        $this->json($method, $url, $this->payload([
            'username' => $method === 'PUT' ? 'admin-user' : 'created-admin',
            'password' => Password::encodeToRequest('new-password'),
            $field => $value,
        ]))->assertUnprocessable()->assertJsonValidationErrors($error, 'data.errors');

        $this->assertSame(2, Admin::query()->count());
        $this->assertSame('Original', $this->admin->refresh()->realname);
    }

    public static function invalidFields(): array
    {
        $cases = [];
        foreach (['POST', 'PUT'] as $method) {
            foreach (['username', 'realname', 'password', 'captcha_rule'] as $field) {
                $cases[] = [$method, $field, ['invalid']];
            }
            $cases[] = [$method, 'roles', [[2]]];
        }

        return $cases;
    }

    public function testRejectsMissingRole(): void
    {
        $this->putJson('/_tests/admins/2', $this->payload(['roles' => [-1]]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('roles', 'data.errors')
            ->assertJsonPath('message', '角色 [-1] 不存在');

        $this->assertSame([2], $this->admin->roles()->pluck('roles.id')->all());
    }

    public function testForbidsAssigningSuperRoleWithoutPermission(): void
    {
        $this->actingAs($this->admin);

        $this->putJson('/_tests/admins/2', $this->payload(['roles' => [1]]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('roles', 'data.errors');

        $this->assertSame([2], $this->admin->roles()->pluck('roles.id')->all());
    }

    public function testSyncsRolesAndInvalidatesLoadedPermissions(): void
    {
        $this->admin->roles()->sync([1]);
        $this->assertTrue($this->admin->hasAllAccess());
        $key = 'auth-access:2';
        Cache::put($key, ['stale']);
        RuntimeCache::put($key, ['stale']);

        $this->putJson('/_tests/admins/2', $this->payload(['roles' => [3, 2, 3]]))
            ->assertSuccessful()->assertJsonPath('data.updated', true);

        $this->assertSame(
            [2, 3], $this->admin->roles()->orderBy('role_id')->pluck('roles.id')->all()
        );
        $this->assertFalse($this->admin->hasAllAccess());
        $this->assertNull(Cache::get($key));
        $this->assertNull(RuntimeCache::get($key));
    }

    public function testKeepsAdministratorRolesUnchanged(): void
    {
        $admin = Admin::query()->findOrFail(1);

        $this->putJson('/_tests/admins/1', $this->payload([
            'username' => $admin->username,
            'roles' => [2, 3],
            'v' => $admin->v,
        ]))->assertSuccessful();

        $this->assertSame([], $admin->roles()->pluck('roles.id')->all());
    }

    #[DataProvider('passwords')]
    public function testUpdatesPasswordOnlyWhenProvided(?string $password): void
    {
        $original = $this->admin->password;

        $this->putJson('/_tests/admins/2', $this->payload([
            'password' => $password ? Password::encodeToRequest($password) : $password,
        ]))->assertSuccessful();

        $this->admin->refresh();
        if (! $password) {
            $this->assertSame($original, $this->admin->password);

            return;
        }

        $this->assertTrue(Password::check(
            Password::encode($password), $this->admin->salt, $this->admin->password
        ));
    }

    public static function passwords(): array
    {
        return [[null], [''], ['new-password']];
    }

    public function testRejectsStaleVersion(): void
    {
        $this->putJson('/_tests/admins/2', $this->payload(['v' => $this->admin->v + 1]))
            ->assertJsonPath('code', Errors::DataVersionMismatch->code());

        $this->assertSame('Original', $this->admin->refresh()->realname);
        $this->assertSame([2], $this->admin->roles()->pluck('roles.id')->all());
    }

    public function testUploadsAndClearsAvatar(): void
    {
        $this->post('/_tests/admins/2/avatar', [
            'file' => UploadedFile::fake()->image('avatar.png'),
        ], ['Accept' => 'application/json'])
            ->assertSuccessful()->assertJsonPath('data.updated', true);

        $this->assertStringEndsWith('.png', $this->admin->refresh()->avatar);
        $this->assertCount(1, Storage::disk('upload')->allFiles());

        $this->postJson('/_tests/admins/2/avatar')
            ->assertSuccessful()->assertJsonPath('data.updated', true);

        $this->assertSame('', $this->admin->refresh()->avatar);
    }

    public function testInvalidFileDoesNotClearAvatar(): void
    {
        $avatar = $this->admin->avatar;

        $this->postJson('/_tests/admins/2/avatar', ['file' => 'invalid'])
            ->assertUnprocessable()->assertJsonValidationErrors('file', 'data.errors');

        $this->assertSame($avatar, $this->admin->refresh()->avatar);
    }

    #[DataProvider('unavailableAdmins')]
    public function testChecksAdminBeforeUploadingAvatar(int $id, int $status): void
    {
        $this->actingAs($this->admin);

        $this->post('/_tests/admins/'.$id.'/avatar', [
            'file' => UploadedFile::fake()->image('avatar.png'),
        ], ['Accept' => 'application/json'])->assertStatus($status);

        $this->assertSame([], Storage::disk('upload')->allFiles());
    }

    public static function unavailableAdmins(): array
    {
        return [[1, 403], [999, 404]];
    }

    public function testForbidsDeletingAdministrator(): void
    {
        $this->deleteJson('/_tests/admins/1')->assertForbidden();

        $this->assertNotNull(Admin::query()->find(1));
    }

    public function testListsAdminsWithRoleFields(): void
    {
        $items = $this->getJson('/_tests/admins')->assertSuccessful()->json('data.items');

        $this->assertCount(2, $items);
        $this->assertSame([['id' => 1, 'name' => 'super']], $items[0]['roles']);
        $this->assertTrue($items[0]['super']);
        $this->assertTrue($items[0]['has_all_access']);
        $this->assertSame([['id' => 2, 'name' => 'editor']], $items[1]['roles']);
        $this->assertFalse($items[1]['super']);
        $this->assertFalse($items[1]['has_all_access']);
        $this->assertArrayNotHasKey('password', $items[1]);
        $this->assertArrayNotHasKey('salt', $items[1]);
    }

    protected function payload(array $data = []): array
    {
        return array_replace([
            'username' => $this->admin->username,
            'realname' => 'Updated',
            'roles' => [2],
            'v' => $this->admin->v,
        ], $data);
    }
}

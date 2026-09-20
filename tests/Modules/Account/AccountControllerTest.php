<?php

declare(strict_types=1);

namespace Tests\Modules\Account;

use App\Models\System\Admin;
use App\Models\System\Role;
use App\Modules\Account\AccountController;
use Illuminate\Http\UploadedFile;
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
use Tests\Models\ModelTestCase;

class AccountControllerTest extends ModelTestCase
{
    protected Admin $account;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate(
            '2026_01_21_213711_create_admins_table.php',
            '2026_01_22_091023_create_roles_table.php',
            '0000_create_menus_table.php',
        );
        config(['pin.access.cache_ttl' => 0]);
        Mail::fake();
        Storage::fake('upload');

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'super'],
            ['id' => 2, 'name' => 'editor'],
        ]);
        foreach ([10, 20, 21] as $id) {
            DB::table('menus')->insert([
                'id' => $id,
                'pid' => match ($id) {
                    10 => 0, 20 => 10, 21 => 20
                },
                'path' => match ($id) {
                    10 => '10', 20 => '10/20', 21 => '10/20/21'
                },
                'name' => 'menu-'.$id,
                'code' => 'menu-'.$id,
                'sort' => $id,
                'type' => $id === 21 ? 'button' : 'menu',
                'icon' => 'menu-icon',
                'route' => '/menu-'.$id,
            ]);
            DB::table('role_menus')->insert(['role_id' => 2, 'menu_id' => $id]);
        }

        $cacher = $this->createStub(Cacher::class);
        $cacher->method('get')->willReturnCallback(static fn (int $id) => Role::query()->find($id));
        $cachers = $this->cacheProperty->getValue();
        $cachers[Role::class] = $cacher;
        $this->cacheProperty->setValue(null, $cachers);

        $this->account = Admin::create([
            'id' => 2,
            'username' => 'account-user',
            'realname' => 'Original',
            'avatar' => 'https://example.test/original.png',
            'password' => Password::encode('original-password'),
        ]);
        $this->account->roles()->attach(2);
        $this->actingAs($this->account);

        Route::get('/_tests/account', [AccountController::class, 'profile']);
        Route::put('/_tests/account', [AccountController::class, 'updateProfile']);
        Route::put('/_tests/account/password', [AccountController::class, 'updatePassword'])
            ->middleware(DecodePassword::class);
        Route::post('/_tests/account/avatar', [AccountController::class, 'updateAvatar']);
    }

    #[DataProvider('profiles')]
    public function testReturnsProfileWithPermissions(bool $menus, bool $super): void
    {
        if ($super) {
            $this->account->roles()->attach(Role::SUPER_ROLE_ID);
        }

        $data = $this->getJson('/_tests/account?menus='.(int) $menus)
            ->assertSuccessful()
            ->assertJsonPath('data.id', $this->account->id)
            ->assertJsonPath('data.has_all_access', $super)
            ->assertJsonPath('data.access_codes', $super ? [] : ['menu-10', 'menu-20', 'menu-21'])
            ->json('data');

        $this->assertArrayNotHasKey('password', $data);
        $this->assertArrayNotHasKey('salt', $data);
        if (! $menus) {
            $this->assertNull($data['menus']);

            return;
        }

        $this->assertSame([10, 20], array_column($data['menus'], 'id'));
        $this->assertSame([
            'id' => 20,
            'pid' => 10,
            'name' => 'menu-20',
            'code' => 'menu-20',
            'enabled' => 1,
            'visible' => 1,
            'icon' => 'menu-icon',
            'path' => '10/20',
            'paths' => [10, 20],
            'route' => '/menu-20',
        ], $data['menus'][1]);
    }

    public static function profiles(): array
    {
        return [[false, false], [true, false], [false, true], [true, true]];
    }

    public function testUpdatesPassword(): void
    {
        $this->putJson('/_tests/account/password', [
            'current_password' => Password::encodeToRequest('original-password'),
            'password' => Password::encodeToRequest('new-password'),
        ])->assertSuccessful()->assertJsonPath('data.updated', true);

        $this->account->refresh();
        $this->assertTrue(Password::check(
            Password::encode('new-password'), $this->account->salt, $this->account->password
        ));
    }

    public function testRejectsWrongCurrentPassword(): void
    {
        $password = $this->account->password;

        $this->putJson('/_tests/account/password', [
            'current_password' => Password::encodeToRequest('wrong-password'),
            'password' => Password::encodeToRequest('new-password'),
        ])->assertJsonPath('code', Errors::UpdateFailed->code())
            ->assertJsonPath('message', '当前密码错误');

        $this->assertSame($password, $this->account->refresh()->password);
    }

    #[DataProvider('passwordFields')]
    public function testRejectsArrayPassword(string $field): void
    {
        $password = $this->account->password;

        $this->putJson('/_tests/account/password', [
            'current_password' => Password::encodeToRequest('original-password'),
            'password' => Password::encodeToRequest('new-password'),
            $field => ['invalid'],
        ])->assertUnprocessable()->assertJsonValidationErrors($field, 'data.errors');

        $this->assertSame($password, $this->account->refresh()->password);
    }

    public static function passwordFields(): array
    {
        return [['current_password'], ['password']];
    }

    public function testUploadsAvatar(): void
    {
        $url = $this->post('/_tests/account/avatar', [
            'file' => UploadedFile::fake()->image('avatar.png'),
        ], ['Accept' => 'application/json'])
            ->assertSuccessful()
            ->assertJsonPath('data.updated', true)
            ->json('data.url');

        $this->assertStringEndsWith('.png', $url);
        $this->assertSame($url, $this->account->refresh()->avatar);
        $this->assertCount(1, Storage::disk('upload')->allFiles());
    }

    public function testClearsAvatarWithoutFile(): void
    {
        $this->postJson('/_tests/account/avatar')
            ->assertSuccessful()
            ->assertJsonPath('data.updated', true)
            ->assertJsonPath('data.url', null);

        $this->assertSame('', $this->account->refresh()->avatar);
    }

    #[DataProvider('invalidFiles')]
    public function testInvalidFileDoesNotClearAvatar(mixed $file): void
    {
        $avatar = $this->account->avatar;

        $this->postJson('/_tests/account/avatar', ['file' => $file])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('file', 'data.errors');

        $this->assertSame($avatar, $this->account->refresh()->avatar);
    }

    public static function invalidFiles(): array
    {
        return [['not-a-file'], [['invalid']]];
    }

    #[DataProvider('avatars')]
    public function testUpdatesOnlyProfileFields(?string $avatar): void
    {
        $password = $this->account->password;

        $this->putJson('/_tests/account', [
            'realname' => 'Updated',
            'avatar' => $avatar,
            'username' => 'unexpected',
            'password' => 'unexpected',
        ])->assertSuccessful()->assertJsonPath('data.updated', true);

        $this->account->refresh();
        $this->assertSame('Updated', $this->account->realname);
        $this->assertSame($avatar ?? '', $this->account->avatar);
        $this->assertSame('account-user', $this->account->username);
        $this->assertSame($password, $this->account->password);
    }

    public static function avatars(): array
    {
        return [[null], ['https://example.test/avatar.png']];
    }

    #[DataProvider('invalidNames')]
    public function testRejectsInvalidName(mixed $name): void
    {
        $this->putJson('/_tests/account', ['realname' => $name])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('realname', 'data.errors');

        $this->assertSame('Original', $this->account->refresh()->realname);
    }

    public static function invalidNames(): array
    {
        return [[['invalid']], [123]];
    }
}

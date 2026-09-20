<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\System\Admin;
use App\Models\System\Role;
use Illuminate\Support\Facades\DB;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Password\PasswordException;
use Pin\Support\Facades\Password;

class AdminTest extends ModelTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate(
            '2026_01_21_213711_create_admins_table.php',
            '2026_01_22_091023_create_roles_table.php',
            '0000_create_menus_table.php',
        );

        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'super'],
            ['id' => 2, 'name' => 'editor'],
            ['id' => 3, 'name' => 'reviewer'],
        ]);
        foreach ([10, 11, 12, 13] as $id) {
            DB::table('menus')->insert([
                'id' => $id,
                'name' => 'menu-'.$id,
                'code' => 'menu-'.$id,
                'path' => (string) $id,
                'sort' => $id,
            ]);
        }
        DB::table('role_admins')->insert([
            ['role_id' => 2, 'uid' => 2],
            ['role_id' => 3, 'uid' => 2],
        ]);
        DB::table('role_menus')->insert([
            ['role_id' => 2, 'menu_id' => 10],
            ['role_id' => 2, 'menu_id' => 11],
            ['role_id' => 3, 'menu_id' => 11],
            ['role_id' => 3, 'menu_id' => 12],
        ]);
    }

    public function testLoadsDistinctMenusWithoutRepeatingRoleQuery(): void
    {
        DB::enableQueryLog();
        $menus = new Admin(['id' => 2])->accessibleMenus();

        $this->assertSame([10, 11, 12], $menus->keys()->all());
        $this->assertCount(2, DB::getQueryLog());
    }

    public function testReloadsRolesAfterSuperRoleIsRevoked(): void
    {
        $admin = new Admin(['id' => 2]);
        $admin->roles()->attach(Role::SUPER_ROLE_ID);
        $this->assertTrue($admin->hasAllAccess());

        $admin->roles()->detach(Role::SUPER_ROLE_ID);

        $this->assertSame([10, 11, 12], $admin->accessibleMenus()->keys()->all());
        $this->assertFalse($admin->hasAllAccess());
    }

    public function testReloadsChangedRoleAssignments(): void
    {
        $admin = new Admin(['id' => 2]);
        $admin->load('roles');
        $admin->roles()->detach(2);

        $this->assertSame([11, 12], $admin->accessibleMenus()->keys()->all());
    }

    public function testAdministratorDoesNotQueryRoles(): void
    {
        DB::enableQueryLog();

        $menus = new Admin(['id' => Admin::ADMINISTRATOR_ID])->accessibleMenus();

        $this->assertSame([10, 11, 12, 13], $menus->keys()->all());
        $this->assertCount(1, DB::getQueryLog());
    }

    public function testSuperRoleGrantsAllMenus(): void
    {
        $admin = new Admin(['id' => 2]);
        $admin->roles()->attach(Role::SUPER_ROLE_ID);

        $this->assertSame([10, 11, 12, 13], $admin->accessibleMenus()->keys()->all());
        $this->assertTrue($admin->hasAllAccess());
    }

    public function testExcludesDeletedRolesAndMenus(): void
    {
        DB::table('roles')->where('id', 3)->update(['deleted_at' => time()]);
        DB::table('menus')->where('id', 10)->update(['deleted_at' => time()]);

        $this->assertSame([11], new Admin(['id' => 2])->accessibleMenus()->keys()->all());
    }

    #[DataProvider('salts')]
    public function testHashesPasswordOnCreation(?string $salt): void
    {
        $encoded = Password::encode('test@123');
        $admin = Admin::create([
            'username' => 'created-admin',
            'realname' => 'Tester',
            'password' => $encoded,
            'salt' => $salt,
        ]);

        $this->assertSame(8, strlen($admin->salt));
        $this->assertTrue(Password::check($encoded, $admin->salt, $admin->password));
        if ($salt) {
            $this->assertSame($salt, $admin->salt);
        }

        $hashed = $admin->password;
        $admin->update(['realname' => 'Updated']);
        $this->assertSame($hashed, $admin->refresh()->password);
        foreach (['password', 'salt', 'role_id', 'deleted_at'] as $attribute) {
            $this->assertArrayNotHasKey($attribute, $admin->toArray());
        }
    }

    public static function salts(): array
    {
        return [[null], ['fixed123']];
    }

    #[DataProvider('invalidPasswords')]
    public function testDoesNotReplaceExplicitInvalidPassword(string $password): void
    {
        $admin = new Admin([
            'password' => Password::encode('original'),
            'salt' => 'fixed123',
        ]);

        $this->expectException(PasswordException::class);

        $admin->hashPassword($password);
    }

    public static function invalidPasswords(): array
    {
        return [[''], ['0']];
    }

    public function testUsesModelPasswordWhenOmitted(): void
    {
        $encoded = Password::encode('original');
        $admin = new Admin(['password' => $encoded, 'salt' => 'fixed123']);

        $this->assertTrue(Password::check($encoded, $admin->salt, $admin->hashPassword()));
    }
}

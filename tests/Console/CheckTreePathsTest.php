<?php

declare(strict_types=1);

namespace Tests\Console;

use App\Console\Commands\CheckTreePaths;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;

class CheckTreePathsTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'console_testing',
            'database.connections.console_testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
        ]);

        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('pid')->default(0);
            $table->unsignedInteger('level')->default(1);
            $table->string('path');
            $table->unsignedInteger('deleted_at')->default(0);
        });
    }

    #[DataProvider('tableOptions')]
    public function testChecksValidMenus(array $options): void
    {
        DB::table('menus')->insert([
            ['id' => 1, 'pid' => 0, 'level' => 1, 'path' => '1'],
            ['id' => 2, 'pid' => 1, 'level' => 2, 'path' => '1/2'],
        ]);

        $this->artisan(CheckTreePaths::class, $options)
            ->expectsOutput('All paths are valid')
            ->assertSuccessful()->run();
    }

    public static function tableOptions(): array
    {
        return [
            'default table' => [[]],
            'explicit table' => [['--table' => 'menus']],
        ];
    }

    public function testRejectsUnsupportedTablesBeforeQuerying(): void
    {
        DB::enableQueryLog();

        $this->artisan(CheckTreePaths::class, ['--table' => 'users'])
            ->expectsOutput('Unsupported table: users')
            ->assertExitCode(Command::INVALID)->run();

        $this->assertSame([], DB::getQueryLog());
    }

    public function testReportsAllPathErrors(): void
    {
        DB::table('menus')->insert([
            ['id' => 1, 'pid' => 0, 'level' => 1, 'path' => '1'],
            ['id' => 2, 'pid' => 1, 'level' => 7, 'path' => '9/2'],
            ['id' => 3, 'pid' => 99, 'level' => 1, 'path' => '3'],
        ]);

        $this->artisan(CheckTreePaths::class)
            ->expectsOutput('2 invalid_level level [7] not equal paths length [2]')
            ->expectsOutput('2 path_mismatch expect=[1,2] got=[9,2]')
            ->expectsOutput('3 parent_not_found parent [99] not exist')
            ->assertFailed()->run();
    }

    public function testExcludesDeletedNodes(): void
    {
        DB::table('menus')->insert([
            ['id' => 1, 'path' => '1', 'deleted_at' => 0],
            ['id' => 2, 'path' => '', 'deleted_at' => 1],
        ]);

        $this->artisan(CheckTreePaths::class)
            ->expectsOutput('All paths are valid')
            ->assertSuccessful()->run();
    }

    public function testAcceptsEmptyTrees(): void
    {
        $this->artisan(CheckTreePaths::class)
            ->expectsOutput('All paths are valid')
            ->assertSuccessful()->run();
    }
}

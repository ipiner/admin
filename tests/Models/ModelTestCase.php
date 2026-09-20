<?php

declare(strict_types=1);

namespace Tests\Models;

use App\Models\System\Admin;
use App\Models\System\Menu;
use App\Models\System\Role;
use App\Models\System\RoleMenu;
use App\Models\Upload;
use App\Models\UploadLog;
use Illuminate\Foundation\Testing\TestCase;
use Override;
use Pin\Models\Cache\Cacher;
use Pin\Models\Model;
use Pin\Support\Facades\RuntimeCache;
use ReflectionProperty;

abstract class ModelTestCase extends TestCase
{
    protected array $cachers;

    protected ReflectionProperty $cacheProperty;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'database.default' => 'models_testing',
            'database.connections.models_testing' => [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
            'pin.modules.log.operation.subject_name_columns' => [],
            'hashing.bcrypt.rounds' => 4,
        ]);
        RuntimeCache::flush();

        $this->cacheProperty = new ReflectionProperty(Model::class, 'cacher');
        $this->cachers = $this->cacheProperty->getValue();
        $cachers = $this->cachers;

        foreach ([
            Admin::class,
            Menu::class,
            Role::class,
            RoleMenu::class,
            Upload::class,
            UploadLog::class,
        ] as $class) {
            $cacher = $this->createStub(Cacher::class);
            $cacher->method('forget')->willReturn(true);
            $cacher->method('getAll')->willReturnCallback(
                static fn () => $class::query()->get()->keyBy('id')
            );
            $cachers[$class] = $cacher;
        }

        $this->cacheProperty->setValue(null, $cachers);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->cacheProperty->setValue(null, $this->cachers);
        RuntimeCache::flush();

        parent::tearDown();
    }

    protected function migrate(string ...$files): void
    {
        foreach ($files as $file) {
            (require database_path('migrations/'.$file))->up();
        }
    }
}

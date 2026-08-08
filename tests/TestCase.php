<?php

declare(strict_types=1);

namespace Tests;

use App\Models\System\Admin;
use App\Modules\Auth\LoginResource;
use Database\Factories\System\AdminFactory;
use Pin\Support\Invoker;
use Pin\Testing\Pest;

Pest::boot();

abstract class TestCase extends \Illuminate\Foundation\Testing\TestCase
{
    /**
     * @var array<int, string>
     */
    protected static array $authTokens = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withAuth(AdminFactory::testingAdmin());
    }

    protected function invoker(string|object $class): Invoker
    {
        return new Invoker($class);
    }

    protected function withAuth(Admin|int $admin): static
    {
        $admin = $admin instanceof Admin ? $admin : Admin::find($admin);
        $this->actingAs($admin);
        $this->withHeader('token', $this->authToken($admin));

        return $this;
    }

    protected function authToken(Admin $admin): string
    {
        if (isset(static::$authTokens[$admin->id])) {
            return static::$authTokens[$admin->id];
        }

        return static::$authTokens[$admin->id] = LoginResource::forAdmin($admin)['token'];
    }
}

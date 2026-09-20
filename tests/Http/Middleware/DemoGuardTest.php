<?php

declare(strict_types=1);

namespace Tests\Http\Middleware;

use App\Errors\Errors;
use App\Events\LoginFailed;
use App\Exceptions\Exception;
use App\Http\Middleware\DemoGuard;
use App\Models\System\Admin;
use App\Models\System\Menu;
use App\Routes\AccountRoute;
use App\Routes\Auth\LoginRoute;
use App\Routes\System\AdminRoute;
use App\Routes\System\MenuRoute;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Event;
use LogicException;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Models\Cache\Cacher;
use Pin\Route\Routable;
use ReflectionProperty;
use Symfony\Component\HttpFoundation\Response;

class DemoGuardTest extends TestCase
{
    protected array $cachers;

    protected ReflectionProperty $cacheProperty;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->cacheProperty = new ReflectionProperty(Admin::class, 'cacher');
        $this->cachers = $this->cacheProperty->getValue();

        $admins = [
            1 => new Admin(['id' => 1, 'username' => 'admin']),
            2 => new Admin(['id' => 2, 'username' => 'test-admin']),
            3 => new Admin(['id' => 3, 'username' => 'other']),
        ];
        $menus = [
            10 => new Menu(['id' => 10, 'type' => Menu::MENU]),
            11 => new Menu(['id' => 11, 'type' => Menu::BUTTON]),
        ];
        $adminCache = $this->createStub(Cacher::class);
        $adminCache->method('get')->willReturnCallback(static fn (int $id) => $admins[$id] ?? null);
        $menuCache = $this->createStub(Cacher::class);
        $menuCache->method('get')->willReturnCallback(static fn (int $id) => $menus[$id] ?? null);

        $this->cacheProperty->setValue(null, [
            ...$this->cachers,
            Admin::class => $adminCache,
            Menu::class => $menuCache,
        ]);

        Event::fake([LoginFailed::class]);
    }

    #[Override]
    protected function tearDown(): void
    {
        $this->cacheProperty->setValue(null, $this->cachers);

        parent::tearDown();
    }

    #[DataProvider('guardedRequests')]
    public function testGuardsRequests(
        Routable $route,
        int $id,
        array $payload,
        ?string $message
    ): void {
        $request = $this->request($route, $id, $payload);

        try {
            $response = $this->handle($request);
        } catch (Exception $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame(403, $e->getCode());
            $this->assertSame('演示环境'.$message, $e->getMessage());

            return;
        }

        $this->assertNull($message, 'The request should have been rejected.');
        $this->assertSame(204, $response->getStatusCode());
    }

    public static function guardedRequests(): array
    {
        $requests = [
            'create menu' => [MenuRoute::Create, 0, ['type' => Menu::MENU], '菜单只能添加按钮'],
            'create button' => [MenuRoute::Create, 0, ['type' => Menu::BUTTON], null],
            'missing type' => [MenuRoute::Create, 0, [], null],
            'convert button to menu' => [
                MenuRoute::Update, 11, ['type' => Menu::MENU], '菜单只能更新按钮',
            ],
            'keep button type' => [MenuRoute::Update, 11, ['type' => Menu::BUTTON], null],
            'missing menu with new type' => [MenuRoute::Update, 99, ['type' => Menu::MENU], null],
            'unused type on status update' => [
                MenuRoute::UpdateEnabled, 11, ['type' => Menu::MENU], null,
            ],
            'delete protected admin' => [AdminRoute::Delete, 2, [], '禁止删除该管理员'],
            'update protected admin' => [AdminRoute::Update, 2, [], '禁止更新该管理员'],
            'delete other admin' => [AdminRoute::Delete, 3, [], null],
            'update other admin' => [AdminRoute::Update, 3, [], null],
            'missing admin' => [AdminRoute::Delete, 99, [], null],
            'update own password' => [AccountRoute::UpdatePassword, 0, [], '该管理员禁止更新密码'],
            'update own profile' => [AccountRoute::UpdateProfile, 0, [], null],
        ];

        foreach ([
            MenuRoute::Update,
            MenuRoute::UpdateEnabled,
            MenuRoute::UpdateVisible,
        ] as $route) {
            $requests[$route->name.' menu'] = [$route, 10, [], '菜单只能更新按钮'];
            $requests[$route->name.' button'] = [$route, 11, [], null];
            $requests[$route->name.' missing'] = [$route, 99, [], null];
        }

        $requests['delete menu'] = [MenuRoute::Delete, 10, [], '菜单只能删除按钮'];
        $requests['delete button'] = [MenuRoute::Delete, 11, [], null];
        $requests['delete missing menu'] = [MenuRoute::Delete, 99, [], null];

        return $requests;
    }

    #[DataProvider('readingMethods')]
    public function testReadingRequestsDoNotResolveUser(string $method): void
    {
        $request = $this->request(MenuRoute::Index);
        $request->setMethod($method);
        $request->setUserResolver(
            static fn () => throw new LogicException('Unexpected user lookup')
        );

        $this->assertSame(204, $this->handle($request)->getStatusCode());
    }

    public static function readingMethods(): array
    {
        return [['GET'], ['HEAD'], ['OPTIONS']];
    }

    public function testNonDemoRequestsDoNotResolveUser(): void
    {
        $request = $this->request(MenuRoute::Delete, 10);
        $request->server->remove('RUNNING_IN_DEMO');
        $request->setUserResolver(
            static fn () => throw new LogicException('Unexpected user lookup')
        );

        $this->assertSame(204, $this->handle($request)->getStatusCode());
    }

    public function testAdministratorBypassesRestrictions(): void
    {
        $request = $this->request(MenuRoute::Delete, 10);
        $request->setUserResolver(static fn () => new Admin(['id' => Admin::ADMINISTRATOR_ID]));

        $this->assertSame(204, $this->handle($request)->getStatusCode());
    }

    #[DataProvider('adminUsernames')]
    public function testRejectsAdminLoginAndRecordsFailure(string $username): void
    {
        try {
            $this->handle($this->request(LoginRoute::Login, payload: ['username' => $username]));
            $this->fail('Admin login should have been rejected.');
        } catch (Exception $e) {
            $this->assertSame(403, $e->getStatusCode());
            $this->assertSame(Errors::LoginDisabled->message(), $e->getMessage());
        }

        Event::assertDispatched(
            LoginFailed::class,
            static fn (LoginFailed $event) => $event->admin->id === Admin::ADMINISTRATOR_ID
                && $event->code === Errors::LoginDisabled->code()
                && $event->internalCode === Errors::LoginDisabled->code()
                && $event->message === Errors::LoginDisabled->message()
        );
    }

    public static function adminUsernames(): array
    {
        return [['admin'], ['ADMIN'], ['AdMiN']];
    }

    public function testAllowsAdminLoginWithCookie(): void
    {
        $request = $this->request(LoginRoute::Login, payload: ['username' => 'admin']);
        $request->cookies->set('admin_login_token', '1');

        $this->assertSame(204, $this->handle($request)->getStatusCode());
        Event::assertNotDispatched(LoginFailed::class);
    }

    public function testLeavesInvalidUsernameToValidation(): void
    {
        $request = $this->request(LoginRoute::Login, payload: ['username' => ['admin']]);

        $this->assertSame(204, $this->handle($request)->getStatusCode());
        Event::assertNotDispatched(LoginFailed::class);
    }

    protected function request(Routable $route, int $id = 0, array $payload = []): Request
    {
        $definition = $route->definition();
        $request = Request::create(
            '/'.ltrim(str_replace('{id}', (string) $id, $definition->uri), '/'),
            $definition->method,
            server: ['CONTENT_TYPE' => 'application/json', 'RUNNING_IN_DEMO' => true],
            content: json_encode($payload, JSON_THROW_ON_ERROR)
        );
        $matchedRoute = new Route($definition->method, $definition->uri, static fn () => null);
        $matchedRoute->name($definition->name)->bind($request);
        $request->setRouteResolver(static fn () => $matchedRoute);
        $request->setUserResolver(
            static fn () => new Admin(['id' => 2, 'username' => 'test-admin'])
        );

        return $request;
    }

    protected function handle(Request $request): Response
    {
        return new DemoGuard()->handle($request, static fn () => new Response(status: 204));
    }
}

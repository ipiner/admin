<?php

declare(strict_types=1);

namespace Tests\Modules\Auth;

use App\Errors\Errors;
use App\Events\LoginFailed;
use App\Events\LoginSucceeded;
use App\Models\System\Admin;
use App\Modules\Auth\LoginController;
use App\Modules\Auth\LoginResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Auth\Auth;
use Pin\Captcha\Errors as CaptchaErrors;
use Pin\Models\Cache\Cacher;
use Pin\Models\Model;
use Pin\Modules\Log\Facades\Log;
use Pin\Modules\Log\Models\ActivityLog;
use Pin\Modules\Log\Models\LoginLog;
use Pin\Modules\Log\Payloads\LoginPayload;
use Pin\Modules\Log\Payloads\Payload;
use Pin\Password\Middleware\DecodePassword;
use Pin\Support\Facades\Password;
use Pin\Support\Facades\RuntimeCache;
use Pin\Token\Exceptions\TokenMissingException;
use RuntimeException;
use Tests\Models\ModelTestCase;

class LoginFlowTest extends ModelTestCase
{
    protected Admin $admin;

    protected array $events = [];

    protected array $logs = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->migrate('2026_01_21_213711_create_admins_table.php');
        config(['cache.default' => 'array']);
        Mail::fake();

        $cacher = $this->createStub(Cacher::class);
        $cacher->method('forget')->willReturn(true);
        $cacher->method('get')->willReturnCallback(
            static fn (int $id) => Admin::query()->find($id)
        );
        $cacher->method('getAll')->willReturnCallback(
            static fn () => Admin::query()->get()->keyBy('id')
        );
        $cachers = $this->cacheProperty->getValue();
        $cachers[Admin::class] = $cacher;
        $this->cacheProperty->setValue(null, $cachers);

        $this->admin = Admin::create([
            'id' => 2,
            'username' => 'login-user',
            'realname' => 'Login User',
            'password' => Password::encode('test-password'),
            'login_num' => 4,
        ]);
        Log::shouldReceive('create')->andReturnUsing(function (Payload $payload): Model {
            $this->logs[] = $payload->toArray();

            return $payload instanceof LoginPayload ? new LoginLog() : new ActivityLog();
        });
        foreach ([LoginFailed::class, LoginSucceeded::class] as $event) {
            Event::listen($event, function (object $event): void {
                $this->events[] = $event;
            });
        }

        Route::post('/_tests/login', [LoginController::class, 'login'])
            ->middleware(DecodePassword::class);
        Route::get('/_tests/logout', [LoginController::class, 'logout']);
    }

    public function testLogsInAndClearsAccessCache(): void
    {
        Cache::put('auth-access:2', ['stale']);
        RuntimeCache::put('auth-access:2', ['stale']);

        $response = $this->postJson('/_tests/login', $this->credentials([
            'username' => 'LOGIN-USER',
        ]))->assertSuccessful()
            ->assertJsonPath('message', '登录成功')
            ->assertJsonPath('data.user', ['id' => 2, 'realname' => 'Login User']);

        $token = Auth::token()->decode($response->json('data.token'));
        $this->assertSame(2, $token->uid);
        $this->assertStringStartsWith('auth-token:2-', $token->jti);
        $this->assertSame(2, auth()->id());
        $this->assertNull(Cache::get('auth-access:2'));
        $this->assertNull(RuntimeCache::get('auth-access:2'));
        $this->admin->refresh();
        $this->assertSame(5, $this->admin->login_num);
        $this->assertSame('127.0.0.1', $this->admin->last_login_ip);
        $this->assertNotNull($this->admin->last_login_at);
        $this->assertCount(1, $this->events);
        $this->assertInstanceOf(LoginSucceeded::class, $this->events[0]);
        $this->assertSame(0, DB::connection()->transactionLevel());
        $this->assertCount(2, $this->logs);
        $this->assertSame(2, $this->logs[0]['uid']);
        $this->assertSame(2, $this->logs[1]['uid']);
    }

    public function testMissingFieldsAreLoggedWithoutLookingUpAdmin(): void
    {
        DB::enableQueryLog();

        $this->postJson('/_tests/login')->assertUnprocessable()
            ->assertJsonValidationErrors(['username', 'password', 'captcha'], 'data.errors');

        $this->assertCount(1, $this->events);
        $this->assertInstanceOf(LoginFailed::class, $this->events[0]);
        $this->assertSame(0, $this->events[0]->admin->id);
        $this->assertSame('', $this->events[0]->admin->username);
        $this->assertCount(0, DB::getQueryLog());
    }

    #[DataProvider('invalidFields')]
    public function testInvalidInputIsLoggedAsValidationFailure(string $field): void
    {
        $this->postJson('/_tests/login', $this->credentials([$field => ['invalid']]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors($field, 'data.errors');

        $this->assertCount(1, $this->events);
        $event = $this->events[0];
        $this->assertInstanceOf(LoginFailed::class, $event);
        $this->assertSame(422, $event->code);
        $this->assertSame(422, $event->internalCode);
        $this->assertArrayHasKey($field, $event->context);
        $this->assertFalse(auth()->hasUser());
        $this->assertSame(4, $this->admin->refresh()->login_num);
    }

    public static function invalidFields(): array
    {
        return [['username'], ['password'], ['captcha']];
    }

    #[DataProvider('usernames')]
    public function testCaptchaFailurePrecedesCredentialFailure(string $username): void
    {
        $this->postJson('/_tests/login', $this->credentials([
            'username' => $username,
            'captcha' => 'plain:a|b',
        ]))->assertUnprocessable()->assertJsonPath('code', CaptchaErrors::CaptchaMismatch->code());

        $this->assertCount(1, $this->events);
        $event = $this->events[0];
        $this->assertInstanceOf(LoginFailed::class, $event);
        $this->assertSame(CaptchaErrors::CaptchaMismatch->code(), $event->internalCode);
        $this->assertSame('plain:a', $event->context['input']);
        $this->assertArrayNotHasKey('err', $event->context);
        $this->assertFalse(auth()->hasUser());
    }

    public static function usernames(): array
    {
        return [['login-user'], ['missing-user']];
    }

    #[DataProvider('credentialFailures')]
    public function testCredentialFailuresKeepInternalCodes(string $username, Errors $error): void
    {
        $this->postJson('/_tests/login', $this->credentials([
            'username' => $username,
            'password' => Password::encodeToRequest('wrong-password'),
        ]))->assertJsonPath('code', Errors::LoginFailed->code())
            ->assertJsonPath('message', Errors::LoginFailed->message());

        $this->assertCount(1, $this->events);
        $event = $this->events[0];
        $this->assertInstanceOf(LoginFailed::class, $event);
        $this->assertSame($error->code(), $event->internalCode);
        $this->assertSame(Errors::LoginFailed->code(), $event->code);
        $this->assertFalse(auth()->hasUser());
    }

    public static function credentialFailures(): array
    {
        return [
            ['missing-user', Errors::LoginAccountNotFound],
            ['login-user', Errors::LoginPasswordMismatch],
        ];
    }

    #[DataProvider('previousUsers')]
    public function testRollsBackFailedLoginProcessing(?int $previousId): void
    {
        if ($previousId) {
            $this->actingAs(new Admin(['id' => $previousId, 'username' => 'previous-user']));
        }
        Event::listen(LoginSucceeded::class, static function (): void {
            throw new RuntimeException('Login listener failed');
        });

        $this->postJson('/_tests/login', $this->credentials())->assertStatus(500);

        $this->assertSame(0, DB::connection()->transactionLevel());
        $this->assertSame(4, $this->admin->refresh()->login_num);
        $this->assertSame($previousId, auth()->user()?->id);
    }

    public static function previousUsers(): array
    {
        return [[null], [9]];
    }

    public function testLogoutRevokesRequestToken(): void
    {
        $token = LoginResource::forAdmin($this->admin)['token'];

        $this->withHeader('token', $token)->getJson('/_tests/logout')
            ->assertSuccessful()->assertJsonPath('message', '退出成功');

        $this->assertFalse(auth()->hasUser());
        $this->assertCount(1, $this->logs);
        $this->assertSame('login.logout', $this->logs[0]['event']);
        $this->assertSame(2, $this->logs[0]['uid']);
        $this->expectException(TokenMissingException::class);

        Auth::token()->decode($token);
    }

    public function testGuestLogoutDoesNotWriteActivityLog(): void
    {
        $this->getJson('/_tests/logout')->assertSuccessful()
            ->assertJsonPath('message', '退出成功');

        $this->assertSame([], $this->logs);
        $this->assertFalse(auth()->hasUser());
    }

    protected function credentials(array $overrides = []): array
    {
        return [
            'username' => 'login-user',
            'password' => Password::encodeToRequest('test-password'),
            'captcha' => 'plain:a|a',
            ...$overrides,
        ];
    }
}

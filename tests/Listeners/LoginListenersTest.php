<?php

declare(strict_types=1);

namespace Tests\Listeners;

use App\Events\LoginFailed;
use App\Events\LoginSucceeded;
use App\Models\System\Admin;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Event;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Models\Model;
use Pin\Modules\Log\Facades\Log;
use Pin\Modules\Log\Models\ActivityLog;
use Pin\Modules\Log\Models\LoginLog;
use Pin\Modules\Log\Payloads\LoginPayload;
use Pin\Modules\Log\Payloads\Payload;
use Pin\Support\Facades\Actor;

class LoginListenersTest extends TestCase
{
    protected array $logs = [];

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Log::shouldReceive('create')->twice()->andReturnUsing(function (Payload $payload): Model {
            $this->logs[] = $payload->toArray();

            return $payload instanceof LoginPayload ? new LoginLog() : new ActivityLog();
        });
    }

    #[DataProvider('actors')]
    public function testSuccessfulLoginUsesEventAdmin(?int $actorId): void
    {
        $actor = $actorId ? new Admin(['id' => $actorId, 'username' => 'actor-'.$actorId]) : null;
        Actor::shouldReceive('user')->andReturn($actor);
        Actor::shouldReceive('type')->andReturn($actor ? 'admin' : 'guest');
        $admin = new Admin(['id' => 42, 'username' => 'logged-in-admin']);

        Event::dispatch(new LoginSucceeded($admin));

        $this->assertCount(2, $this->logs);
        [$login, $activity] = $this->logs;
        $this->assertSame(42, $login['uid']);
        $this->assertSame('logged-in-admin', $login['username']);
        $this->assertSame(0, $login['code']);
        $this->assertSame('登录成功', $login['message']);
        $this->assertSame(42, $activity['uid']);
        $this->assertSame('logged-in-admin', $activity['username']);
        $this->assertSame('login.succeeded', $activity['event']);
        $this->assertSame('登录系统', $activity['title']);
        $this->assertSame(0, $activity['subject_id']);
        $this->assertSame('系统', $activity['subject_name']);
        $this->assertSame('system', $activity['subject_type']);
    }

    public static function actors(): array
    {
        return [
            'guest' => [null],
            'different admin' => [99],
            'same admin with different username' => [42],
        ];
    }

    #[DataProvider('failureContexts')]
    public function testFailedLoginPreservesEventDetails(int $id, array $context): void
    {
        Actor::shouldReceive('user')->andReturn(new Admin(['id' => 99, 'username' => 'other']));
        Actor::shouldReceive('type')->andReturn('admin');
        $admin = new Admin(['id' => $id, 'username' => 'attempted-admin']);
        $event = new LoginFailed($admin, 21000, '帐号或密码错误', 21002, $context);

        Event::dispatch($event);

        $this->assertCount(2, $this->logs);
        [$login, $activity] = $this->logs;
        $this->assertSame($id, $login['uid']);
        $this->assertSame('attempted-admin', $login['username']);
        $this->assertSame(21002, $login['code']);
        $this->assertSame('帐号或密码错误', $login['message']);
        $this->assertSame($context, $login['context']);
        $this->assertSame($id, $activity['uid']);
        $this->assertSame('attempted-admin', $activity['username']);
        $this->assertSame('login.failed', $activity['event']);
        $this->assertSame('登录失败', $activity['title']);
        $this->assertSame(0, $activity['subject_id']);
        $this->assertSame('系统', $activity['subject_name']);
        $this->assertSame('system', $activity['subject_type']);
        $this->assertSame(21002, $activity['context']['code']);
        $this->assertSame('帐号或密码错误', $activity['context']['message']);
        $this->assertSame(
            array_diff_key($context, ['code' => null, 'message' => null]),
            array_diff_key($activity['context'], ['code' => null, 'message' => null])
        );
        $this->assertSame($context, $event->context);
    }

    public static function failureContexts(): array
    {
        return [
            'empty' => [42, []],
            'details' => [42, ['captcha' => ['attempts' => 3]]],
            'conflicting fields' => [42, [
                'code' => 0,
                'message' => 'overridden',
                'captcha' => ['attempts' => 3],
            ]],
            'unknown admin' => [0, ['username' => 'attempted-admin']],
        ];
    }
}

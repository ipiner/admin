<?php

declare(strict_types=1);

namespace Tests\Mail;

use App\Mail\Mail;
use App\Mail\MailQueue;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Mail as Mailer;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Exceptions\Exception;
use RuntimeException;

class MailTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Mailer::fake();
        config([
            'app.debug' => false,
            'mail.from.address' => 'errors@example.test',
        ]);
    }

    public function testCreatesMailFromThrowable(): void
    {
        $e = new RuntimeException('test error', 100);
        $mail = Mail::fromThrowable($e);

        $this->assertInstanceOf(Mail::class, $mail);
        $this->assertSame('test error', $mail->log['message']);
        $this->assertSame([
            'code' => 100,
            'class' => RuntimeException::class,
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
            'context' => [],
        ], $mail->log['context']['exception']);
    }

    public function testIncludesBusinessExceptionContext(): void
    {
        $mail = Mail::fromThrowable(new Exception('error')->withContext(['order_id' => 123]));

        $this->assertSame(['order_id' => 123], $mail->log['context']['exception']['context']);
    }

    public function testAllowsLogOverrides(): void
    {
        $mail = Mail::fromThrowable(new RuntimeException('original'), [
            'message' => 'custom message',
            'context' => ['custom' => true],
            'extra' => ['source' => 'test'],
        ]);

        $this->assertSame('custom message', $mail->log['message']);
        $this->assertSame(['custom' => true], $mail->log['context']);
        $this->assertSame(['source' => 'test'], $mail->log['extra']);
    }

    public function testDoesNotSendMailInDebugMode(): void
    {
        config(['app.debug' => true]);

        $this->assertFalse(Mail::sendByThrowable(new RuntimeException('error')));
        $this->assertFalse(Mail::sendByThrowable(new RuntimeException('error'), queue: false));
        Mailer::assertNothingOutgoing();
    }

    #[DataProvider('sendingModes')]
    public function testSendsThrowableMail(string $class, bool $queue): void
    {
        $mail = $class::sendByThrowable(new RuntimeException('something wrong'), queue: $queue);

        $this->assertInstanceOf($queue ? MailQueue::class : $class, $mail);
        $mail->assertTo('errors@example.test');
        $mail->assertHasSubject('[testing] something wrong');
        $mail->assertSeeInHtml('"message": "something wrong"');

        if ($queue) {
            Mailer::assertQueued(MailQueue::class, 1);
            Mailer::assertNothingSent();
        } else {
            Mailer::assertSent($class, 1);
            Mailer::assertNothingQueued();
        }
    }

    public static function sendingModes(): array
    {
        return [
            'immediate' => [Mail::class, false],
            'queued' => [Mail::class, true],
            'queue class sent immediately' => [MailQueue::class, false],
            'queue class queued' => [MailQueue::class, true],
        ];
    }

    public function testSubclassCanSendQueuedMail(): void
    {
        $prototype = new class([]) extends Mail
        {
        };

        $mail = $prototype::sendByThrowable(new RuntimeException('custom mail'));

        $this->assertInstanceOf(MailQueue::class, $mail);
        Mailer::assertQueued(MailQueue::class, 1);
    }

    public function testKeepsSubclassForImmediateMail(): void
    {
        $prototype = new class([]) extends Mail
        {
        };

        $mail = $prototype::sendByThrowable(new RuntimeException('custom mail'), queue: false);

        $this->assertInstanceOf($prototype::class, $mail);
        Mailer::assertSent($prototype::class, 1);
        Mailer::assertNothingQueued();
    }
}

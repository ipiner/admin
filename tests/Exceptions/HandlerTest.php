<?php

declare(strict_types=1);

namespace Tests\Exceptions;

use App\Exceptions\Exception;
use App\Exceptions\Handler;
use App\Mail\Mail;
use DomainException;
use Illuminate\Cache\ArrayStore;
use Illuminate\Cache\RateLimiter;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Cache\Repository;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Mail as Mailer;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

class HandlerTest extends TestCase
{
    protected Handler $handler;

    protected LoggerInterface&MockObject $logger;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->handler = $this->app->make(Handler::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->app->instance(LoggerInterface::class, $this->logger);

        Mailer::fake();
        config(['app.debug' => false]);
    }

    public function testReportsExceptionAndSendsMail(): void
    {
        $this->logger->expects($this->once())->method('error')->with('something wrong');

        $this->handler->report(new RuntimeException('something wrong'));

        Mailer::assertQueued(Mail::class);
    }

    public function testDoesNotSendMailForTransportException(): void
    {
        $this->logger->expects($this->once())->method('error');

        $this->handler->report(new TransportException('smtp error'));

        Mailer::assertNothingQueued();
    }

    public function testDoesNotSendMailForTransportExceptionInterface(): void
    {
        $exception = new class extends RuntimeException implements TransportExceptionInterface
        {
            public function getDebug(): string
            {
                return '';
            }

            public function appendDebug(string $debug): void
            {
            }
        };

        $this->handler->report($exception);

        Mailer::assertNothingQueued();
    }

    public function testIgnoresExceptionsThrownWhileSendingMail(): void
    {
        $this->logger->expects($this->once())->method('error');
        $handler = new class($this->app) extends Handler
        {
            #[Override]
            protected function mailThrowable(Throwable $e): Mail|false
            {
                throw new RuntimeException('mail failed');
            }
        };

        $handler->report(new RuntimeException('something wrong'));

        Mailer::assertNothingQueued();
    }

    public function testReportsAndMailsDuplicateExceptionOnce(): void
    {
        $this->logger->expects($this->once())->method('error');
        $this->handler->dontReportDuplicates();
        $exception = new RuntimeException('repeated error');

        $this->handler->report($exception);
        $this->handler->report($exception);

        Mailer::assertQueued(Mail::class, 1);
    }

    public function testThrottlesMailAndLogsTogether(): void
    {
        $this->app->instance(RateLimiter::class, new RateLimiter(new Repository(new ArrayStore())));
        $this->logger->expects($this->once())->method('error');
        $this->handler->throttleUsing(
            static fn (Throwable $e) => Limit::perMinute(1)->by('handler-test')
        );

        $this->handler->report(new RuntimeException('first error'));
        $this->handler->report(new RuntimeException('second error'));

        Mailer::assertQueued(Mail::class, 1);
    }

    public function testSendsMailForMappedException(): void
    {
        $this->logger->expects($this->once())->method('error')->with('mapped error');
        $this->handler->map(
            RuntimeException::class,
            static fn (RuntimeException $e) => new DomainException('mapped error', 123, $e)
        );

        $this->handler->report(new RuntimeException('original error'));

        Mailer::assertQueued(
            Mail::class,
            static fn (Mail $mail) => $mail->log['message'] === 'mapped error'
                && $mail->log['context']['exception']['class'] === DomainException::class
                && $mail->log['context']['exception']['code'] === 123
        );
    }

    public function testDoesNotSendMailForIgnoredMappedException(): void
    {
        $this->logger->expects($this->never())->method('error');
        $this->handler->map(
            RuntimeException::class,
            static fn (RuntimeException $e) => new Exception('ignored')->withReport(false)
        );

        $this->handler->report(new RuntimeException('original error'));

        Mailer::assertNothingQueued();
    }

    public function testDoesNotSendMailForNonReportableExceptions(): void
    {
        $this->logger->expects($this->never())->method('error');

        foreach ([
            new Exception('forbidden')->withStatusCode(403),
            new Exception('ignored')->withReport(false),
            new NotFoundHttpException('not found'),
        ] as $exception) {
            $this->handler->report($exception);
        }

        Mailer::assertNothingQueued();
    }

    public function testDoesNotSendMailInDebugMode(): void
    {
        config(['app.debug' => true]);
        $this->logger->expects($this->once())->method('error');

        $this->handler->report(new RuntimeException('debug error'));

        Mailer::assertNothingQueued();
    }
}

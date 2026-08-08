<?php

declare(strict_types=1);

use App\Exceptions\Handler;
use App\Mail\Mail;
use Illuminate\Support\Facades\Mail as Mailer;
use Symfony\Component\Mailer\Exception\TransportException;

beforeEach(function () {
    $this->handler = app(Handler::class);
    Mailer::fake();
    config(['app.debug' => false]);
});

it('reports exception and sends mail', function () {
    $this->handler->report(new RuntimeException('something wrong'));
    Mailer::assertQueued(Mail::class);
});

it('does not send mail for transport exception', function () {
    $this->handler->report(new TransportException('smtp error'));
    Mailer::assertNothingQueued();
});

it('ignores exceptions thrown while sending mail', function () {
    $handler = new class($this->app) extends Handler
    {
        public function mailThrowable(Throwable $e): Mail|false
        {
            throw new Exception('something wrong');
        }
    };
    $handler->report(new Exception('something wrong'));
    Mailer::assertNothingQueued();
});

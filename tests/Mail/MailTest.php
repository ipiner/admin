<?php

declare(strict_types=1);

use App\Mail\Mail;

it('creates mail from throwable', function () {
    $e = new RuntimeException('test error', 100);
    $mail = Mail::fromThrowable($e);

    expect($mail)
        ->toBeInstanceOf(Mail::class)
        ->and($mail->log['message'])->toBe('test error')
        ->and($mail->log['context']['exception']['code'])->toBe(100)
        ->and($mail->log['context']['exception']['class'])->toBe(RuntimeException::class);
});

it('does not send mail in debug mode', function () {
    config(['app.debug' => true]);
    expect(Mail::sendByThrowable(new RuntimeException('error')))
        ->toBe(false);
});

it('sends throwable mail', function (bool $queue) {
    Illuminate\Support\Facades\Mail::fake();
    config(['app.debug' => false]);
    $mail = Mail::sendByThrowable(
        new RuntimeException('something wrong'),
        [],
        $queue
    );

    $mail->assertHasSubject('[testing] something wrong');
    $mail->assertSeeInHtml('"message": "something wrong"');

    if ($queue) {
        Illuminate\Support\Facades\Mail::assertQueued(Mail::class);
    } else {
        Illuminate\Support\Facades\Mail::assertSent(Mail::class);
    }
})->with([
    'sent' => false,
    'queued' => true,
]);

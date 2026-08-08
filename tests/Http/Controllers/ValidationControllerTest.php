<?php

declare(strict_types=1);

use App\Routes\ValidationRoute;
use Pin\Errors\Errors;
use Pin\Errors\IError;

it('validates password is valid', function (IError $err, string $value) {
    ValidationRoute::Password->testJson($this, ['password' => 'plain:'.$value])
        ->assertCode($err->code());
})->with([
    [Errors::None, 'test@123'],
    [Errors::PasswordTooShort, '123456'],
    [Errors::PasswordTooLong, 'PasswordTooLongPasswordTooLongPasswordTooLong'],
    [Errors::PasswordSequenceTooLong, '1234564568#'],
    [Errors::PasswordTooManyRepeats, '1111111111'],
    [Errors::PasswordContainsWhitespace, '11    11 11 1'],
    [Errors::PasswordInsufficientTypes, 'testtest'],
]);

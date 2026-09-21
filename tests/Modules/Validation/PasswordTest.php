<?php

declare(strict_types=1);

use App\Routes\ValidationRoute;
use Pin\Errors\Errors;
use Pin\Errors\IError;

it('validates passwords', function (IError $error, string $value) {
    ValidationRoute::Password->testJson($this, ['password' => 'plain:'.$value])
        ->assertStatus($error === Errors::None ? 200 : 422)
        ->assertJsonPath('code', $error->code());
})->with([
    [Errors::None, 'test@123'],
    [Errors::PasswordTooShort, '123456'],
    [Errors::PasswordTooLong, 'PasswordTooLongPasswordTooLongPasswordTooLong'],
    [Errors::PasswordSequenceTooLong, '1234564568#'],
    [Errors::PasswordTooManyRepeats, '1111111111'],
    [Errors::PasswordContainsWhitespace, '11    11 11 1'],
    [Errors::PasswordInsufficientTypes, 'testtest'],
]);

it('rejects invalid password payloads', function (array $payload) {
    ValidationRoute::Password->testJson($this, $payload)
        ->assertStatus(422)
        ->assertJsonPath('code', Errors::ValidationFailed->code())
        ->assertJsonStructure(['data' => ['errors' => ['password']]]);
})->with([
    'missing' => [[]],
    'empty' => [['password' => '']],
    'null' => [['password' => null]],
    'array' => [['password' => ['test@123']]],
]);

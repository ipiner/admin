<?php

declare(strict_types=1);

namespace Tests\Http\Controllers;

use App\Http\Controllers\ValidationController;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Pin\Crypt\Middleware\Decrypt;
use Pin\Errors\Errors;
use Pin\Errors\IError;

class ValidationControllerTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
        Route::post('/_tests/password', [ValidationController::class, 'password'])
            ->middleware(Decrypt::class.':password');
    }

    #[DataProvider('passwords')]
    public function testValidatesPasswords(IError $error, string $value): void
    {
        $this->postJson('/_tests/password', ['password' => 'plain:'.$value])
            ->assertStatus($error === Errors::None ? 200 : 422)
            ->assertJsonPath('code', $error->code());
    }

    public static function passwords(): array
    {
        return [
            [Errors::None, 'test@123'],
            [Errors::PasswordTooShort, '123456'],
            [Errors::PasswordTooLong, 'PasswordTooLongPasswordTooLongPasswordTooLong'],
            [Errors::PasswordSequenceTooLong, '1234564568#'],
            [Errors::PasswordTooManyRepeats, '1111111111'],
            [Errors::PasswordContainsWhitespace, '11    11 11 1'],
            [Errors::PasswordInsufficientTypes, 'testtest'],
        ];
    }

    #[DataProvider('invalidPayloads')]
    public function testRejectsInvalidPayloads(array $payload): void
    {
        $this->postJson('/_tests/password', $payload)
            ->assertStatus(422)
            ->assertJsonPath('code', Errors::ValidationFailed->code())
            ->assertJsonStructure(['data' => ['errors' => ['password']]]);
    }

    public static function invalidPayloads(): array
    {
        return [
            'missing' => [[]],
            'empty' => [['password' => '']],
            'null' => [['password' => null]],
            'array' => [['password' => ['test@123']]],
        ];
    }
}

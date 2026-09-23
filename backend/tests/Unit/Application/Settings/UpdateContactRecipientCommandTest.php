<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Settings;

use App\Application\Settings\UpdateContactRecipient\UpdateContactRecipientCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateContactRecipientCommandTest extends TestCase
{
    private function validator(): ValidatorInterface
    {
        return Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();
    }

    private function messages(ConstraintViolationListInterface $violations): string
    {
        $messages = [];
        foreach ($violations as $violation) {
            $messages[] = (string) $violation->getMessage();
        }

        return implode(' | ', $messages);
    }

    public function testValidValuesPass(): void
    {
        $violations = $this->validator()->validate(
            new UpdateContactRecipientCommand('panel@example.com', 'Nombre del panel'),
        );

        self::assertCount(0, $violations);
    }

    public function testEmptyValuesAreValid(): void
    {
        // Es la aserción que sostiene la semántica de "vaciar = volver al .env":
        // si esto fallara, el panel no podría deshacer un ajuste.
        $violations = $this->validator()->validate(new UpdateContactRecipientCommand('', ''));

        self::assertCount(0, $violations);
    }

    public function testTrimsWhitespaceOnConstruction(): void
    {
        $command = new UpdateContactRecipientCommand('  panel@example.com  ', '  Nombre  ');

        self::assertSame('panel@example.com', $command->email);
        self::assertSame('Nombre', $command->name);
    }

    public function testEmailWithSurroundingWhitespaceIsValid(): void
    {
        // Copiar y pegar arrastra espacios: no debe fallar un email correcto.
        $violations = $this->validator()->validate(
            new UpdateContactRecipientCommand('  panel@example.com  ', 'Nombre'),
        );

        self::assertCount(0, $violations);
    }

    public function testMalformedEmailIsRejected(): void
    {
        $violations = $this->validator()->validate(
            new UpdateContactRecipientCommand('no-es-email', 'Nombre del panel'),
        );

        self::assertStringContainsString('El email no es válido', $this->messages($violations));
    }

    public function testTooLongEmailIsRejected(): void
    {
        $email = str_repeat('a', 250) . '@example.com';

        $violations = $this->validator()->validate(
            new UpdateContactRecipientCommand($email, 'Nombre del panel'),
        );

        self::assertStringContainsString('255 caracteres', $this->messages($violations));
    }

    public function testTooLongNameIsRejected(): void
    {
        $violations = $this->validator()->validate(
            new UpdateContactRecipientCommand('panel@example.com', str_repeat('a', 151)),
        );

        self::assertStringContainsString('150 caracteres', $this->messages($violations));
    }
}

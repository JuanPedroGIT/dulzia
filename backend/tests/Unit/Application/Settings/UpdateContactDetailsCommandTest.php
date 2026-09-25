<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Settings;

use App\Application\Settings\UpdateContactDetails\UpdateContactDetailsCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UpdateContactDetailsCommandTest extends TestCase
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

    /** @return iterable<string, array{string}> */
    public static function validPhones(): iterable
    {
        yield 'con prefijo y espacios'   => ['+34 629 991 659'];
        yield 'sin espacios'             => ['+34629991659'];
        yield 'sin prefijo'              => ['629991659'];
        yield 'con guiones'              => ['629-991-659'];
        yield 'con puntos'               => ['629.991.659'];
        yield 'con paréntesis'           => ['+34 (629) 991 659'];
    }

    public function testValidValuesPass(): void
    {
        $violations = $this->validator()->validate(
            new UpdateContactDetailsCommand('hola@example.com', '+34 629 991 659'),
        );

        self::assertCount(0, $violations);
    }

    public function testEmptyValuesAreValid(): void
    {
        // Es la aserción que sostiene la semántica de "vaciar = volver al valor
        // por defecto de la web": si esto fallara, el panel no podría deshacerlo.
        $violations = $this->validator()->validate(new UpdateContactDetailsCommand('', ''));

        self::assertCount(0, $violations);
    }

    #[DataProvider('validPhones')]
    public function testAcceptsTheUsualPhoneFormats(string $phone): void
    {
        $violations = $this->validator()->validate(new UpdateContactDetailsCommand('', $phone));

        self::assertCount(0, $violations);
    }

    public function testTrimsWhitespaceOnConstruction(): void
    {
        $command = new UpdateContactDetailsCommand('  hola@example.com  ', '  +34 629 991 659  ');

        self::assertSame('hola@example.com', $command->email);
        self::assertSame('+34 629 991 659', $command->phone);
    }

    public function testMalformedEmailIsRejected(): void
    {
        $violations = $this->validator()->validate(
            new UpdateContactDetailsCommand('no-es-email', '+34 629 991 659'),
        );

        self::assertStringContainsString('El email no es válido', $this->messages($violations));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidPhones(): iterable
    {
        yield 'letras'          => ['llámame'];
        yield 'pocos dígitos'   => ['12345'];
        yield 'demasiados'      => ['+34 629 991 659 123456'];
        yield 'mezcla rara'     => ['629 991 659 ext 12'];
    }

    #[DataProvider('invalidPhones')]
    public function testMalformedPhoneIsRejected(string $phone): void
    {
        $violations = $this->validator()->validate(new UpdateContactDetailsCommand('', $phone));

        self::assertStringContainsString('El teléfono no es válido', $this->messages($violations));
    }

    public function testTooLongPhoneIsRejected(): void
    {
        $phone = '+' . str_repeat('1', 40);

        $violations = $this->validator()->validate(new UpdateContactDetailsCommand('', $phone));

        self::assertStringContainsString('30 caracteres', $this->messages($violations));
    }

    public function testTooLongEmailIsRejected(): void
    {
        $email = str_repeat('a', 250) . '@example.com';

        $violations = $this->validator()->validate(new UpdateContactDetailsCommand($email, ''));

        self::assertStringContainsString('255 caracteres', $this->messages($violations));
    }
}

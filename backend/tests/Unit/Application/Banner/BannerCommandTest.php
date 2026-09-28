<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Banner;

use App\Application\Banner\CreateBanner\CreateBannerCommand;
use App\Application\Banner\UpdateBanner\UpdateBannerCommand;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class BannerCommandTest extends TestCase
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

    private function create(string $title, string $description, string $startsAt, string $endsAt): CreateBannerCommand
    {
        return new CreateBannerCommand($title, $description, $startsAt, $endsAt);
    }

    public function testValidBannerPasses(): void
    {
        $violations = $this->validator()->validate(
            $this->create('Nuevo servicio', 'Ya estamos en toda la península.', '2026-10-01', '2026-10-31'),
        );

        self::assertCount(0, $violations);
    }

    public function testSameDayWindowPasses(): void
    {
        // Un aviso de un solo día es válido: los bordes son inclusive.
        $violations = $this->validator()->validate(
            $this->create('Solo hoy', 'Último día.', '2026-10-01', '2026-10-01'),
        );

        self::assertCount(0, $violations);
    }

    public function testTrimsWhitespaceOnConstruction(): void
    {
        $command = new UpdateBannerCommand(
            'id',
            '  Título  ',
            '  Descripción  ',
            '  2026-10-01  ',
            '  2026-10-31  ',
        );

        self::assertSame('Título', $command->title);
        self::assertSame('Descripción', $command->description);
        self::assertSame('2026-10-01', $command->startsAt);
        self::assertSame('2026-10-31', $command->endsAt);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidDates(): iterable
    {
        yield 'mes inexistente' => ['2026-13-01'];
        yield 'día inexistente' => ['2026-02-31'];
        yield 'texto suelto'     => ['mañana'];
        yield 'con hora'         => ['2026-10-01 10:00'];
    }

    #[DataProvider('invalidDates')]
    public function testMalformedStartsAtIsRejected(string $startsAt): void
    {
        $violations = $this->validator()->validate(
            $this->create('Título', 'Descripción', $startsAt, '2026-10-31'),
        );

        self::assertStringContainsString('La fecha de inicio no es válida', $this->messages($violations));
    }

    public function testBlankFieldsAreRejected(): void
    {
        $violations = $this->validator()->validate(
            $this->create('', '', '', ''),
        );

        $messages = $this->messages($violations);
        self::assertStringContainsString('El título es obligatorio', $messages);
        self::assertStringContainsString('La descripción es obligatoria', $messages);
        self::assertStringContainsString('La fecha de inicio es obligatoria', $messages);
        self::assertStringContainsString('La fecha de fin es obligatoria', $messages);
    }

    public function testEndsBeforeStartsIsRejected(): void
    {
        $violations = $this->validator()->validate(
            $this->create('Título', 'Descripción', '2026-10-31', '2026-10-01'),
        );

        self::assertStringContainsString(
            'La fecha de fin no puede ser anterior a la de inicio',
            $this->messages($violations),
        );
    }

    public function testTooLongTitleIsRejected(): void
    {
        $violations = $this->validator()->validate(
            $this->create(str_repeat('a', 121), 'Descripción', '2026-10-01', '2026-10-31'),
        );

        self::assertStringContainsString('120 caracteres', $this->messages($violations));
    }

    public function testTooLongDescriptionIsRejected(): void
    {
        $violations = $this->validator()->validate(
            $this->create('Título', str_repeat('a', 501), '2026-10-01', '2026-10-31'),
        );

        self::assertStringContainsString('500 caracteres', $this->messages($violations));
    }
}

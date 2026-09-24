<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Category;

use App\Application\Category\CategoryCommandFactory;
use App\Domain\Shared\InvalidInputException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class CategoryCommandFactoryTest extends TestCase
{
    private function jsonRequest(array $body): Request
    {
        return Request::create(
            '/api/admin/categories',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($body, JSON_THROW_ON_ERROR),
        );
    }

    public function testCreatesCommandAndTrimsTheName(): void
    {
        $command = (new CategoryCommandFactory())->createFromRequest($this->jsonRequest([
            'name' => '  Animación  ',
            'emoji' => '🎪',
            'sort_order' => 3,
        ]));

        self::assertSame('Animación', $command->name);
        self::assertSame('🎪', $command->emoji);
        self::assertSame(3, $command->sortOrder);
    }

    public function testEmptyEmojiBecomesNull(): void
    {
        $command = (new CategoryCommandFactory())->createFromRequest($this->jsonRequest([
            'name' => 'Animación',
            'emoji' => '   ',
        ]));

        self::assertNull($command->emoji);
        // Sin orden explícito, la nueva categoría va al principio de la lista.
        self::assertSame(0, $command->sortOrder);
    }

    public function testBuildsUpdateCommandWithId(): void
    {
        $command = (new CategoryCommandFactory())->updateFromRequest('food', $this->jsonRequest([
            'name' => 'Comida',
        ]));

        self::assertSame('food', $command->id);
        self::assertSame('Comida', $command->name);
    }

    public function testThrowsWhenNameIsMissing(): void
    {
        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage('name es requerido');

        (new CategoryCommandFactory())->createFromRequest($this->jsonRequest(['name' => '  ']));
    }
}

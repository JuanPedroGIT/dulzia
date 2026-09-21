<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\ServiceCommandFactory;
use App\Domain\Shared\InvalidInputException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class ServiceCommandFactoryTest extends TestCase
{
    private function jsonRequest(array $body): Request
    {
        return Request::create(
            '/api/admin/services',
            'POST',
            [],
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($body, JSON_THROW_ON_ERROR),
        );
    }

    public function testCreatesCommandFromJsonBody(): void
    {
        $command = (new ServiceCommandFactory())->createFromRequest($this->jsonRequest([
            'name' => '  Candy Bar  ',
            'emoji' => '🍬',
            'description' => 'Descripción',
            'features' => [' Chuches ', '', 'Personalizado'],
            'category' => 'food',
        ]));

        self::assertSame('Candy Bar', $command->name);
        self::assertSame('🍬', $command->emoji);
        self::assertSame('Descripción', $command->description);
        self::assertSame(['Chuches', 'Personalizado'], $command->features);
        self::assertSame('food', $command->category);
    }

    public function testDefaultsCategoryToFood(): void
    {
        $command = (new ServiceCommandFactory())->createFromRequest($this->jsonRequest([
            'name' => 'X', 'emoji' => 'E', 'description' => 'D',
        ]));

        self::assertSame('food', $command->category);
        self::assertSame([], $command->features);
    }

    public function testBuildsUpdateCommandWithId(): void
    {
        $command = (new ServiceCommandFactory())->updateFromRequest('candy-bar', $this->jsonRequest([
            'name' => 'Candy XL', 'emoji' => '🍭', 'description' => 'D', 'features' => [], 'category' => 'food',
        ]));

        self::assertSame('candy-bar', $command->id);
        self::assertSame('Candy XL', $command->name);
    }

    public function testThrowsInvalidInputWhenRequiredFieldsMissing(): void
    {
        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage('name, emoji y description son requeridos');

        (new ServiceCommandFactory())->createFromRequest($this->jsonRequest([
            'name' => '', 'emoji' => '', 'description' => '',
        ]));
    }
}

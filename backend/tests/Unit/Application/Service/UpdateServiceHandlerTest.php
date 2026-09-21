<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\UpdateService\UpdateServiceCommand;
use App\Application\Service\UpdateService\UpdateServiceHandler;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class UpdateServiceHandlerTest extends TestCase
{
    public function testUpdatesExistingService(): void
    {
        $service = TestFactory::service(id: 'candy-bar');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save')->with($service);

        (new UpdateServiceHandler($repo))->handle(new UpdateServiceCommand(
            id: 'candy-bar',
            name: 'Candy Bar XL',
            emoji: '🍭',
            description: 'Nueva descripción',
            features: ['A', 'B'],
            category: 'food',
        ));

        self::assertSame('Candy Bar XL', $service->getName());
        self::assertSame('🍭', $service->getEmoji());
        self::assertSame('Nueva descripción', $service->getDescription());
        self::assertSame(['A', 'B'], $service->getFeatures());
        self::assertSame('food', $service->getCategory());
    }

    public function testThrowsWhenServiceNotFound(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->never())->method('save');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Servicio no encontrado');

        (new UpdateServiceHandler($repo))->handle(new UpdateServiceCommand(
            id: 'no-existe',
            name: 'X',
            emoji: 'E',
            description: 'D',
            features: [],
            category: 'food',
        ));
    }
}

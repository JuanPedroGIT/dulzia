<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\SetFeatured\SetFeaturedCommand;
use App\Application\Service\SetFeatured\SetFeaturedHandler;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class SetFeaturedHandlerTest extends TestCase
{
    public function testMarksServiceAsFeatured(): void
    {
        $service = TestFactory::service(id: 'candy-bar');
        self::assertFalse($service->isFeatured());

        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save')->with($service);

        (new SetFeaturedHandler($repo))->handle(new SetFeaturedCommand('candy-bar', true));

        self::assertTrue($service->isFeatured());
    }

    public function testUnmarksService(): void
    {
        $service = TestFactory::service(id: 'candy-bar', isFeatured: true);

        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);

        (new SetFeaturedHandler($repo))->handle(new SetFeaturedCommand('candy-bar', false));

        self::assertFalse($service->isFeatured());
    }

    public function testDoesNotTouchOtherFields(): void
    {
        $service = TestFactory::service(
            id: 'candy-bar',
            name: 'Candy Bar',
            emoji: '🍬',
            imageUrl: 'https://fake-storage.test/services/propia.jpg',
        );

        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save');

        (new SetFeaturedHandler($repo))->handle(new SetFeaturedCommand('candy-bar', true));

        self::assertSame('Candy Bar', $service->getName());
        self::assertSame('🍬', $service->getEmoji());
        self::assertSame('https://fake-storage.test/services/propia.jpg', $service->getImageUrl());
        self::assertTrue($service->isActive());
    }

    public function testThrowsWhenServiceNotFound(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->never())->method('save');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Servicio no encontrado');

        (new SetFeaturedHandler($repo))->handle(new SetFeaturedCommand('no-existe', true));
    }
}

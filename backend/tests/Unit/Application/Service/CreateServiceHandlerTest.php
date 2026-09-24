<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\CreateService\CreateServiceCommand;
use App\Application\Service\CreateService\CreateServiceHandler;
use App\Application\Service\CreateService\ServiceIdGenerator;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Storage\FileStorageInterface;
use App\Entity\Service;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class CreateServiceHandlerTest extends TestCase
{
    private function handler(ServiceRepositoryInterface $repo, ?FileStorageInterface $storage = null): CreateServiceHandler
    {
        return new CreateServiceHandler(
            $repo,
            new ServiceIdGenerator($repo),
            $storage ?? $this->createMock(FileStorageInterface::class),
        );
    }

    public function testCreatesServiceWithSlugIdAndNextSortOrder(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->method('nextSortOrder')->willReturn(3);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('store');

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Service $service): bool {
                self::assertSame('candy-bar', $service->getId());
                self::assertSame('Candy Bar', $service->getName());
                self::assertSame('🍬', $service->getEmoji());
                self::assertSame('Candy bar para eventos', $service->getDescription());
                self::assertSame(['Chuches', 'Personalizado'], $service->getFeatures());
                self::assertSame('food', $service->getCategory());
                self::assertSame(3, $service->getSortOrder());
                self::assertNull($service->getImageUrl());
                self::assertTrue($service->isActive());

                return true;
            }));

        $result = ($this->handler($repo, $storage))->handle(new CreateServiceCommand(
            name: 'Candy Bar',
            emoji: '🍬',
            description: 'Candy bar para eventos',
            features: ['Chuches', 'Personalizado'],
            category: 'food',
        ));

        self::assertSame(['id' => 'candy-bar', 'name' => 'Candy Bar'], $result);
    }

    public function testSlugifiesAccentsAndSpecialChars(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->method('nextSortOrder')->willReturn(1);

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Service $service): bool {
                self::assertSame('photocall-360', $service->getId());

                return true;
            }));

        ($this->handler($repo))->handle(new CreateServiceCommand(
            name: 'Photocall 360°',
            emoji: '📸',
            description: 'Photocall giratorio',
            features: [],
            category: 'animacion',
        ));
    }

    public function testAppendsSuffixWhenIdAlreadyExists(): void
    {
        $existing = new Service('candy-bar', 'Otro candy', '🍭', 'd', [], 'food');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($existing);
        $repo->method('nextSortOrder')->willReturn(1);

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Service $service): bool {
                self::assertMatchesRegularExpression('/^candy-bar-\d{4}$/', $service->getId());

                return true;
            }));

        ($this->handler($repo))->handle(new CreateServiceCommand(
            name: 'Candy Bar',
            emoji: '🍬',
            description: 'd',
            features: [],
            category: 'food',
        ));
    }

    public function testStoresUploadedImageAsCover(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->method('nextSortOrder')->willReturn(1);

        $file = TestFactory::uploadedFile();
        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->once())
            ->method('store')
            ->with($file)
            ->willReturn('https://fake-storage.test/services/nueva.jpg');

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Service $service): bool {
                self::assertSame('https://fake-storage.test/services/nueva.jpg', $service->getImageUrl());

                return true;
            }));

        ($this->handler($repo, $storage))->handle(new CreateServiceCommand(
            name: 'Candy Bar',
            emoji: '',
            description: 'd',
            features: [],
            category: 'food',
            image: $file,
        ));
    }
}

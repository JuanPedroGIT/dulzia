<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\CategoryResolver;
use App\Application\Service\CreateService\CreateServiceCommand;
use App\Application\Service\CreateService\CreateServiceHandler;
use App\Application\Service\CreateService\ServiceIdGenerator;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\InvalidInputException;
use App\Domain\Storage\FileStorageInterface;
use App\Entity\Category;
use App\Entity\Service;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CreateServiceHandlerTest extends TestCase
{
    /**
     * Resolver real con el repositorio mockeado: devuelve una categoría para
     * cualquier id (la validación se prueba aparte).
     */
    private function resolver(): CategoryResolver
    {
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->method('findById')->willReturnCallback(
            static fn (string $id): Category => new Category($id, ucfirst($id)),
        );

        return new CategoryResolver($categories);
    }

    private function handler(ServiceRepositoryInterface $repo, ?FileStorageInterface $storage = null): CreateServiceHandler
    {
        return new CreateServiceHandler(
            $repo,
            new ServiceIdGenerator($repo),
            $storage ?? $this->createMock(FileStorageInterface::class),
            $this->resolver(),
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

    public function testRejectsAnUnknownCategory(): void
    {
        // Sin clave foránea en la BD, esta validación es la que impide guardar una
        // sección con una categoría inventada.
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->never())->method('save');

        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->method('findById')->willReturn(null);

        $handler = new CreateServiceHandler(
            $repo,
            new ServiceIdGenerator($repo),
            $this->createMock(FileStorageInterface::class),
            new CategoryResolver($categories),
        );

        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage('La categoría "inventada" no existe');

        $handler->handle(new CreateServiceCommand(
            name: 'Candy Bar',
            emoji: '',
            description: 'd',
            features: [],
            category: 'inventada',
        ));
    }

    public function testStoresUploadedThumbnailNextToCover(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->method('nextSortOrder')->willReturn(1);

        $image = TestFactory::uploadedFile();
        $thumbnail = TestFactory::uploadedFile();
        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->exactly(2))
            ->method('store')
            ->willReturnCallback(static fn(UploadedFile $file): string => $file === $thumbnail
                ? 'https://fake-storage.test/services/mini.jpg'
                : 'https://fake-storage.test/services/grande.jpg');

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Service $service): bool {
                self::assertSame('https://fake-storage.test/services/grande.jpg', $service->getImageUrl());
                self::assertSame('https://fake-storage.test/services/mini.jpg', $service->getThumbnailUrl());

                return true;
            }));

        ($this->handler($repo, $storage))->handle(new CreateServiceCommand(
            name: 'Candy Bar',
            emoji: '',
            description: 'd',
            features: [],
            category: 'food',
            image: $image,
            thumbnail: $thumbnail,
        ));
    }
}

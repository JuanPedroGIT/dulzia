<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\CategoryResolver;
use App\Application\Service\UpdateService\UpdateServiceCommand;
use App\Application\Service\UpdateService\UpdateServiceHandler;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\InvalidInputException;
use App\Domain\Shared\NotFoundException;
use App\Domain\Storage\FileStorageInterface;
use App\Entity\Category;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UpdateServiceHandlerTest extends TestCase
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

    private function handler(ServiceRepositoryInterface $repo, ?FileStorageInterface $storage = null): UpdateServiceHandler
    {
        return new UpdateServiceHandler(
            $repo,
            $storage ?? $this->createMock(FileStorageInterface::class),
            $this->resolver(),
        );
    }

    private function command(
        ?UploadedFile $image = null,
        bool $removeImage = false,
        ?string $emoji = '🍭',
        ?UploadedFile $thumbnail = null,
    ): UpdateServiceCommand {
        return new UpdateServiceCommand(
            id: 'candy-bar',
            name: 'Candy Bar XL',
            emoji: $emoji,
            description: 'Nueva descripción',
            features: ['A', 'B'],
            category: 'food',
            image: $image,
            thumbnail: $thumbnail,
            removeImage: $removeImage,
        );
    }

    public function testUpdatesExistingService(): void
    {
        $service = TestFactory::service(id: 'candy-bar');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save')->with($service);

        ($this->handler($repo))->handle($this->command());

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

        ($this->handler($repo))->handle($this->command());
    }

    public function testKeepsCoverImageWhenNoFileIsSent(): void
    {
        $service = TestFactory::service(id: 'candy-bar', imageUrl: 'https://fake-storage.test/services/vieja.jpg');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('store');
        $storage->expects($this->never())->method('delete');

        ($this->handler($repo, $storage))->handle($this->command());

        self::assertSame('https://fake-storage.test/services/vieja.jpg', $service->getImageUrl());
    }

    public function testReplacesCoverImageAndDeletesThePreviousOne(): void
    {
        $service = TestFactory::service(id: 'candy-bar', imageUrl: 'https://fake-storage.test/services/vieja.jpg');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save')->with($service);

        $file = TestFactory::uploadedFile();
        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->once())
            ->method('store')
            ->with($file)
            ->willReturn('https://fake-storage.test/services/nueva.jpg');
        $storage->expects($this->once())
            ->method('delete')
            ->with('https://fake-storage.test/services/vieja.jpg');

        ($this->handler($repo, $storage))->handle($this->command(image: $file));

        self::assertSame('https://fake-storage.test/services/nueva.jpg', $service->getImageUrl());
    }

    public function testRemovesCoverImage(): void
    {
        $service = TestFactory::service(id: 'candy-bar', imageUrl: 'https://fake-storage.test/services/vieja.jpg');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save')->with($service);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('store');
        $storage->expects($this->once())
            ->method('delete')
            ->with('https://fake-storage.test/services/vieja.jpg');

        ($this->handler($repo, $storage))->handle($this->command(removeImage: true));

        self::assertNull($service->getImageUrl());
    }

    public function testRemovingWithoutCoverImageDoesNotTouchStorage(): void
    {
        $service = TestFactory::service(id: 'candy-bar');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('store');
        $storage->expects($this->never())->method('delete');

        ($this->handler($repo, $storage))->handle($this->command(removeImage: true));

        self::assertNull($service->getImageUrl());
    }

    public function testKeepsEmojiWhenCommandSendsNone(): void
    {
        // El panel ya no manda emoji: los 11 servicios lo conservan de respaldo.
        $service = TestFactory::service(id: 'candy-bar', emoji: '🍬');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);

        ($this->handler($repo))->handle($this->command(emoji: null));

        self::assertSame('🍬', $service->getEmoji());
    }

    public function testDeletesPreviousCoverOnlyAfterSaving(): void
    {
        $service = TestFactory::service(id: 'candy-bar', imageUrl: 'https://fake-storage.test/services/vieja.jpg');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->method('save')->willThrowException(new \RuntimeException('BD caída'));

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('delete');

        try {
            ($this->handler($repo, $storage))->handle($this->command(removeImage: true));
            self::fail('Se esperaba la excepción del repositorio');
        } catch (\RuntimeException) {
            // Si el guardado falla, la foto anterior debe seguir en el storage.
        }
    }

    public function testReplacesCoverThumbnailAndDeletesBothOldObjects(): void
    {
        $service = TestFactory::service(
            id: 'candy-bar',
            imageUrl: 'https://fake-storage.test/services/vieja.jpg',
            thumbnailUrl: 'https://fake-storage.test/services/vieja-mini.jpg',
        );
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save')->with($service);

        $image = TestFactory::uploadedFile();
        $thumbnail = TestFactory::uploadedFile();
        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->exactly(2))
            ->method('store')
            ->willReturnCallback(static fn(UploadedFile $file): string => $file === $thumbnail
                ? 'https://fake-storage.test/services/nueva-mini.jpg'
                : 'https://fake-storage.test/services/nueva.jpg');

        $deleted = [];
        $storage->expects($this->exactly(2))
            ->method('delete')
            ->willReturnCallback(static function (string $url) use (&$deleted): void {
                $deleted[] = $url;
            });

        ($this->handler($repo, $storage))->handle($this->command(image: $image, thumbnail: $thumbnail));

        self::assertSame('https://fake-storage.test/services/nueva.jpg', $service->getImageUrl());
        self::assertSame('https://fake-storage.test/services/nueva-mini.jpg', $service->getThumbnailUrl());
        self::assertSame(
            ['https://fake-storage.test/services/vieja.jpg', 'https://fake-storage.test/services/vieja-mini.jpg'],
            $deleted
        );
    }

    public function testRemovesCoverImageAndItsThumbnail(): void
    {
        $service = TestFactory::service(
            id: 'candy-bar',
            imageUrl: 'https://fake-storage.test/services/vieja.jpg',
            thumbnailUrl: 'https://fake-storage.test/services/vieja-mini.jpg',
        );
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save')->with($service);

        $deleted = [];
        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('store');
        $storage->expects($this->exactly(2))
            ->method('delete')
            ->willReturnCallback(static function (string $url) use (&$deleted): void {
                $deleted[] = $url;
            });

        ($this->handler($repo, $storage))->handle($this->command(removeImage: true));

        self::assertNull($service->getImageUrl());
        self::assertNull($service->getThumbnailUrl());
        self::assertContains('https://fake-storage.test/services/vieja-mini.jpg', $deleted);
    }

    public function testCoverUploadWithoutThumbnailLeavesThumbnailEmpty(): void
    {
        $service = TestFactory::service(
            id: 'candy-bar',
            imageUrl: 'https://fake-storage.test/services/vieja.jpg',
            thumbnailUrl: 'https://fake-storage.test/services/vieja-mini.jpg',
        );
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->once())
            ->method('store')
            ->willReturn('https://fake-storage.test/services/nueva.jpg');
        $storage->expects($this->exactly(2))->method('delete');

        ($this->handler($repo, $storage))->handle($this->command(image: TestFactory::uploadedFile()));

        // Sin miniatura nueva, el frontend cae a la foto grande.
        self::assertNull($service->getThumbnailUrl());
        self::assertSame('https://fake-storage.test/services/nueva.jpg', $service->getDisplayThumbnail());
    }

    public function testRejectsAnUnknownCategory(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(TestFactory::service(id: 'candy-bar'));
        $repo->expects($this->never())->method('save');

        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->method('findById')->willReturn(null);

        $handler = new UpdateServiceHandler(
            $repo,
            $this->createMock(FileStorageInterface::class),
            new CategoryResolver($categories),
        );

        $this->expectException(InvalidInputException::class);
        $this->expectExceptionMessage('La categoría "inventada" no existe');

        $handler->handle(new UpdateServiceCommand(
            id: 'candy-bar',
            name: 'Candy Bar XL',
            emoji: null,
            description: 'D',
            features: [],
            category: 'inventada',
        ));
    }
}

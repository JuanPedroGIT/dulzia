<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\UpdatePhoto\UpdatePhotoCommand;
use App\Application\Service\UpdatePhoto\UpdatePhotoHandler;
use App\Domain\Service\ServiceExampleRepositoryInterface;
use App\Domain\Storage\FileStorageInterface;
use App\Domain\Shared\NotFoundException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class UpdatePhotoHandlerTest extends TestCase
{
    private const OLD_URL = 'https://fake-storage.test/services/vieja.jpg';

    private function example(): \App\Entity\ServiceExample
    {
        return TestFactory::example(TestFactory::service(), imageUrl: self::OLD_URL);
    }

    public function testReplacesFileDeletingOldOneFromStorage(): void
    {
        $example = $this->example();
        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('findById')->willReturn($example);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->once())->method('delete')->with(self::OLD_URL);
        $storage->method('store')->willReturn('https://fake-storage.test/services/nueva.jpg');

        $examples->expects($this->once())->method('save')->with($example);

        $result = (new UpdatePhotoHandler($examples, $storage))->handle(new UpdatePhotoCommand(
            photoId: 'abc',
            title: null,
            description: null,
            imageUrl: null,
            file: TestFactory::uploadedFile(),
        ));

        self::assertSame('https://fake-storage.test/services/nueva.jpg', $result['imageUrl']);
        self::assertSame('https://fake-storage.test/services/nueva.jpg', $example->getImageUrl());
    }

    public function testReplacesImageUrlWithoutTouchingStorage(): void
    {
        $example = $this->example();
        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('findById')->willReturn($example);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('delete');
        $storage->expects($this->never())->method('store');

        (new UpdatePhotoHandler($examples, $storage))->handle(new UpdatePhotoCommand(
            photoId: 'abc',
            title: 'Nuevo título',
            description: null,
            imageUrl: 'https://picsum.photos/300',
            file: null,
        ));

        self::assertSame('https://picsum.photos/300', $example->getImageUrl());
        self::assertSame('Nuevo título', $example->getTitle());
        self::assertSame('Descripción de la foto', $example->getDescription());
    }

    public function testKeepsEverythingWhenNoChangesProvided(): void
    {
        $example = $this->example();
        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('findById')->willReturn($example);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('delete');
        $storage->expects($this->never())->method('store');

        (new UpdatePhotoHandler($examples, $storage))->handle(new UpdatePhotoCommand(
            photoId: 'abc',
            title: null,
            description: null,
            imageUrl: null,
            file: null,
        ));

        self::assertSame(self::OLD_URL, $example->getImageUrl());
    }

    public function testThrowsWhenPhotoNotFound(): void
    {
        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('findById')->willReturn(null);
        $examples->expects($this->never())->method('save');

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('store');
        $storage->expects($this->never())->method('delete');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Foto no encontrada');

        (new UpdatePhotoHandler($examples, $storage))->handle(new UpdatePhotoCommand(
            photoId: 'no-existe',
            title: 'T',
            description: null,
            imageUrl: null,
            file: null,
        ));
    }

    public function testReplacesThumbnailAlongWithThePhoto(): void
    {
        $example = TestFactory::example(
            TestFactory::service(),
            imageUrl: self::OLD_URL,
            thumbnailUrl: 'https://fake-storage.test/services/vieja-mini.jpg',
        );
        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('findById')->willReturn($example);

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

        (new UpdatePhotoHandler($examples, $storage))->handle(new UpdatePhotoCommand(
            photoId: 'abc',
            title: null,
            description: null,
            imageUrl: null,
            file: $image,
            thumbnail: $thumbnail,
        ));

        self::assertSame('https://fake-storage.test/services/nueva.jpg', $example->getImageUrl());
        self::assertSame('https://fake-storage.test/services/nueva-mini.jpg', $example->getThumbnailUrl());
        self::assertSame([self::OLD_URL, 'https://fake-storage.test/services/vieja-mini.jpg'], $deleted);
    }

    public function testDropsThumbnailWhenReplacingWithExternalUrl(): void
    {
        $example = TestFactory::example(
            TestFactory::service(),
            imageUrl: self::OLD_URL,
            thumbnailUrl: 'https://fake-storage.test/services/vieja-mini.jpg',
        );
        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('findById')->willReturn($example);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('store');
        // La miniatura era del fichero que se descarta: se borra.
        $storage->expects($this->once())
            ->method('delete')
            ->with('https://fake-storage.test/services/vieja-mini.jpg');

        (new UpdatePhotoHandler($examples, $storage))->handle(new UpdatePhotoCommand(
            photoId: 'abc',
            title: null,
            description: null,
            imageUrl: 'https://picsum.photos/300',
            file: null,
        ));

        self::assertSame('https://picsum.photos/300', $example->getImageUrl());
        self::assertNull($example->getThumbnailUrl());
        self::assertSame('https://picsum.photos/300', $example->getDisplayThumbnail());
    }
}

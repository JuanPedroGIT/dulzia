<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\AddPhoto\AddPhotoCommand;
use App\Application\Service\AddPhoto\AddPhotoHandler;
use App\Domain\Service\ServiceExampleRepositoryInterface;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Entity\ServiceExample;
use App\Domain\Storage\FileStorageInterface;
use App\Domain\Shared\NotFoundException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class AddPhotoHandlerTest extends TestCase
{
    public function testStoresFileAndSavesExample(): void
    {
        $service = TestFactory::service();
        $services = $this->createMock(ServiceRepositoryInterface::class);
        $services->method('findById')->willReturn($service);

        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('nextSortOrderForService')->willReturn(2);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->method('store')->willReturn('https://fake-storage.test/services/nueva.jpg');

        $examples->expects($this->once())
            ->method('save')
            ->with($this->callback(function (ServiceExample $example) use ($service): bool {
                self::assertSame($service, $example->getService());
                self::assertSame('Foto', $example->getTitle());
                self::assertSame('Desc', $example->getDescription());
                self::assertSame('https://fake-storage.test/services/nueva.jpg', $example->getImageUrl());
                self::assertSame(2, $example->getSortOrder());

                return true;
            }));

        $result = (new AddPhotoHandler($services, $examples, $storage))->handle(new AddPhotoCommand(
            serviceId: 'servicio-test',
            title: 'Foto',
            description: 'Desc',
            imageUrl: '',
            file: TestFactory::uploadedFile(),
        ));

        self::assertSame('https://fake-storage.test/services/nueva.jpg', $result['imageUrl']);
        self::assertSame('Foto', $result['title']);
        self::assertSame('Desc', $result['description']);
    }

    public function testUsesProvidedImageUrlWhenNoFile(): void
    {
        $services = $this->createMock(ServiceRepositoryInterface::class);
        $services->method('findById')->willReturn(TestFactory::service());

        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('nextSortOrderForService')->willReturn(1);

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('store');

        $examples->expects($this->once())
            ->method('save')
            ->with($this->callback(function (ServiceExample $example): bool {
                self::assertSame('https://picsum.photos/200', $example->getImageUrl());

                return true;
            }));

        $result = (new AddPhotoHandler($services, $examples, $storage))->handle(new AddPhotoCommand(
            serviceId: 'servicio-test',
            title: 'Foto externa',
            description: 'Desc',
            imageUrl: 'https://picsum.photos/200',
            file: null,
        ));

        self::assertSame('https://picsum.photos/200', $result['imageUrl']);
    }

    public function testThrowsWhenServiceNotFound(): void
    {
        $services = $this->createMock(ServiceRepositoryInterface::class);
        $services->method('findById')->willReturn(null);

        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('store');
        $examples->expects($this->never())->method('save');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Servicio no encontrado');

        (new AddPhotoHandler($services, $examples, $storage))->handle(new AddPhotoCommand(
            serviceId: 'no-existe',
            title: 'Foto',
            description: 'Desc',
            imageUrl: '',
            file: TestFactory::uploadedFile(),
        ));
    }
}

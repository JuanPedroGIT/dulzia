<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\DeletePhoto\DeletePhotoCommand;
use App\Application\Service\DeletePhoto\DeletePhotoHandler;
use App\Domain\Service\ServiceExampleRepositoryInterface;
use App\Domain\Storage\FileStorageInterface;
use App\Domain\Shared\NotFoundException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class DeletePhotoHandlerTest extends TestCase
{
    public function testDeletesFromStorageBeforeRemovingRecord(): void
    {
        $example = TestFactory::example(
            TestFactory::service(),
            imageUrl: 'https://fake-storage.test/services/abc.jpg',
        );

        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('findById')->willReturn($example);

        $storage = $this->createMock(FileStorageInterface::class);
        $storageDeleted = false;
        $storage->expects($this->once())
            ->method('delete')
            ->with('https://fake-storage.test/services/abc.jpg')
            ->willReturnCallback(function () use (&$storageDeleted): void {
                $storageDeleted = true;
            });

        // El borrado del registro debe ocurrir después del borrado en storage
        $examples->expects($this->once())
            ->method('delete')
            ->with($example)
            ->willReturnCallback(function () use (&$storageDeleted): void {
                self::assertTrue($storageDeleted, 'El storage debe borrarse antes que el registro');
            });

        (new DeletePhotoHandler($examples, $storage))->handle(new DeletePhotoCommand('abc'));
    }

    public function testThrowsWhenPhotoNotFound(): void
    {
        $examples = $this->createMock(ServiceExampleRepositoryInterface::class);
        $examples->method('findById')->willReturn(null);
        $examples->expects($this->never())->method('delete');

        $storage = $this->createMock(FileStorageInterface::class);
        $storage->expects($this->never())->method('delete');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Foto no encontrada');

        (new DeletePhotoHandler($examples, $storage))->handle(new DeletePhotoCommand('no-existe'));
    }
}

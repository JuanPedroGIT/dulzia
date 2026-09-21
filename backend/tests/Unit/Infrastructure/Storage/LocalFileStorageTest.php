<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Storage;

use App\Infrastructure\Storage\LocalFileStorage;
use App\Domain\Storage\InvalidFileException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class LocalFileStorageTest extends TestCase
{
    private string $uploadDir;
    private LocalFileStorage $storage;

    protected function setUp(): void
    {
        $this->uploadDir = sys_get_temp_dir() . '/dulzia-local-' . bin2hex(random_bytes(4));
        $this->storage = new LocalFileStorage($this->uploadDir, 'https://example.test');
    }

    protected function tearDown(): void
    {
        foreach (glob($this->uploadDir . '/*') ?: [] as $file) {
            unlink($file);
        }
        @rmdir($this->uploadDir);
    }

    public function testStoreMovesFileAndReturnsUrl(): void
    {
        $url = $this->storage->store(TestFactory::uploadedFile());

        self::assertMatchesRegularExpression(
            '#^https://example\.test/uploads/services/[0-9a-f]{32}\.png$#',
            $url,
        );
        self::assertCount(1, glob($this->uploadDir . '/*'));
    }

    public function testStoreRejectsInvalidFile(): void
    {
        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Archivo inválido');

        $this->storage->store(TestFactory::invalidUploadedFile());
    }

    public function testStoreRejectsNonImageMime(): void
    {
        $this->expectException(InvalidFileException::class);
        $this->expectExceptionMessage('Tipo de archivo no permitido');

        $this->storage->store(TestFactory::nonImageFile());
    }

    public function testDeleteRemovesStoredFile(): void
    {
        $url = $this->storage->store(TestFactory::uploadedFile());
        self::assertCount(1, glob($this->uploadDir . '/*'));

        $this->storage->delete($url);

        self::assertCount(0, glob($this->uploadDir . '/*'));
    }

    public function testDeleteIgnoresExternalUrls(): void
    {
        $url = $this->storage->store(TestFactory::uploadedFile());

        // URLs externas: no deben borrar nada
        $this->storage->delete('https://picsum.photos/200');
        $this->storage->delete('https://otro-dominio.test/uploads/services/f.jpg');

        self::assertCount(1, glob($this->uploadDir . '/*'), 'El archivo propio sigue intacto');
        self::assertStringStartsWith('https://example.test/', $url);
    }
}

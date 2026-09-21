<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Storage;

use App\Infrastructure\Storage\CloudflareR2Storage;
use App\Domain\Storage\InvalidFileException;
use App\Tests\Support\TestFactory;
use Aws\CommandInterface;
use Aws\MockHandler;
use Aws\Result;
use Aws\S3\S3Client;
use PHPUnit\Framework\TestCase;

final class CloudflareR2StorageTest extends TestCase
{
    private MockHandler $handler;
    private CloudflareR2Storage $storage;

    protected function setUp(): void
    {
        $this->handler = new MockHandler();

        $client = new S3Client([
            'region' => 'auto',
            'version' => 'latest',
            'endpoint' => 'https://test.r2.cloudflarestorage.com',
            'credentials' => ['key' => 'test-key', 'secret' => 'test-secret'],
            'handler' => $this->handler,
        ]);

        $this->storage = new CloudflareR2Storage(
            accountId: 'test-account',
            accessKeyId: 'test-key',
            accessKeySecret: 'test-secret',
            bucket: 'dulzia-test',
            publicUrl: 'https://pub-test.r2.dev',
            client: $client,
        );
    }

    public function testStorePutsObjectUnderServicesKeyAndReturnsPublicUrl(): void
    {
        $this->handler->append(new Result(['ETag' => '"abc"']));

        $url = $this->storage->store(TestFactory::uploadedFile());

        self::assertMatchesRegularExpression(
            '#^https://pub-test\.r2\.dev/services/[0-9a-f]{32}\.png$#',
            $url,
        );

        /** @var CommandInterface $command */
        $command = $this->handler->getLastCommand();
        self::assertSame('PutObject', $command->getName());
        self::assertSame('dulzia-test', $command['Bucket']);
        self::assertMatchesRegularExpression('#^services/[0-9a-f]{32}\.png$#', $command['Key']);
        self::assertSame('image/png', $command['ContentType']);
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

    public function testDeleteRemovesObjectForOwnUrls(): void
    {
        $this->handler->append(new Result([]));

        $this->storage->delete('https://pub-test.r2.dev/services/abc.png');

        /** @var CommandInterface $command */
        $command = $this->handler->getLastCommand();
        self::assertSame('DeleteObject', $command->getName());
        self::assertSame('dulzia-test', $command['Bucket']);
        self::assertSame('services/abc.png', $command['Key']);
    }

    public function testDeleteIgnoresExternalAndLegacyUrls(): void
    {
        // Mock queue vacío: cualquier llamada al cliente lanzaría una excepción,
        // así que llegar al final demuestra que no hubo llamada.
        $this->storage->delete('https://picsum.photos/200');
        $this->storage->delete('https://dulzia.example/uploads/services/abc.jpg');
        $this->storage->delete('https://pub-test.r2.dev');   // sin key

        self::assertCount(0, $this->handler);
    }
}

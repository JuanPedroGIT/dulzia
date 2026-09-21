<?php

declare(strict_types=1);

namespace App\Infrastructure\Storage;

use App\Domain\Storage\FileStorageInterface;
use App\Domain\Storage\InvalidFileException;
use Aws\S3\S3Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class CloudflareR2Storage implements FileStorageInterface
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    private S3Client $client;
    private string $bucket;
    private string $publicUrl;

    public function __construct(
        string $accountId,
        string $accessKeyId,
        string $accessKeySecret,
        string $bucket,
        string $publicUrl,
        ?S3Client $client = null,   // inyectable en tests (MockHandler); null = cliente real
    ) {
        $this->bucket    = $bucket;
        $this->publicUrl = rtrim($publicUrl, '/');

        $this->client = $client ?? new S3Client([
            'region'  => 'auto',
            'version' => 'latest',
            'endpoint' => "https://{$accountId}.r2.cloudflarestorage.com",
            'credentials' => [
                'key'    => $accessKeyId,
                'secret' => $accessKeySecret,
            ],
        ]);
    }

    public function store(UploadedFile $file): string
    {
        // Misma validación que LocalFileStorage → ApiExceptionListener lo convierte en 400
        if (!$file->isValid()) {
            throw new InvalidFileException('Archivo inválido o corrupto: ' . $file->getErrorMessage());
        }

        $mime = $file->getMimeType();
        if ($mime === null || !in_array($mime, self::ALLOWED_MIMES, true)) {
            throw new InvalidFileException('Tipo de archivo no permitido: ' . ($mime ?? 'desconocido'));
        }

        $ext      = $file->guessExtension() ?? 'jpg';
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $key      = 'services/' . $filename;

        $realPath = $file->getRealPath();
        if ($realPath === false) {
            throw new InvalidFileException('No se pudo leer el archivo subido');
        }

        $this->client->putObject([
            'Bucket'      => $this->bucket,
            'Key'         => $key,
            'Body'        => fopen($realPath, 'rb'),
            'ContentType' => $mime,
        ]);

        // La BD sigue guardando la URL completa (contrato intacto)
        return $this->publicUrl . '/' . $key;
    }

    public function delete(string $url): void
    {
        // Protección equivalente a la de LocalFileStorage: URLs externas (picsum)
        // o legacy (/uploads/services/) no son nuestras → no-op.
        if (!str_starts_with($url, $this->publicUrl . '/')) {
            return;
        }

        $key = substr($url, strlen($this->publicUrl) + 1);
        if ($key === '') {
            return;
        }

        $this->client->deleteObject([
            'Bucket' => $this->bucket,
            'Key'    => $key,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\TestDoubles;

use App\Domain\Storage\FileStorageInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Storage fake para el entorno de test: evita llamadas reales a Cloudflare R2.
 * Devuelve URLs falsas deterministas y registra las eliminaciones.
 */
final class FakeFileStorage implements FileStorageInterface
{
    /** @var string[] URLs eliminadas durante el test */
    public array $deletedUrls = [];

    public function store(UploadedFile $file): string
    {
        $ext = $file->guessExtension() ?? 'jpg';

        return 'https://fake-storage.test/services/' . bin2hex(random_bytes(16)) . '.' . $ext;
    }

    public function delete(string $url): void
    {
        if (str_starts_with($url, 'https://fake-storage.test/')) {
            $this->deletedUrls[] = $url;
        }
    }
}

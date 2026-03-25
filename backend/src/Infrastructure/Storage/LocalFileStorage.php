<?php

namespace App\Infrastructure\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final class LocalFileStorage implements FileStorageInterface
{
    private const ALLOWED_MIMES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    public function __construct(
        private string $uploadDir,
        private string $baseUrl,
    ) {}

    public function store(UploadedFile $file): string
    {
        if (!in_array($file->getMimeType(), self::ALLOWED_MIMES, true)) {
            throw new \InvalidArgumentException('Tipo de archivo no permitido');
        }

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }

        $ext      = $file->guessExtension() ?? 'jpg';
        $filename = bin2hex(random_bytes(16)) . '.' . $ext;
        $file->move($this->uploadDir, $filename);

        return rtrim($this->baseUrl, '/') . '/uploads/services/' . $filename;
    }

    public function delete(string $url): void
    {
        if (!str_contains($url, '/uploads/services/')) {
            return;
        }

        $path = $this->uploadDir . '/' . basename(parse_url($url, PHP_URL_PATH));
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

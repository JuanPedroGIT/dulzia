<?php

namespace App\Infrastructure\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileStorageInterface
{
    public function store(UploadedFile $file): string;

    public function delete(string $url): void;
}

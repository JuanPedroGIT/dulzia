<?php

namespace App\Domain\Storage;

use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileStorageInterface
{
    public function store(UploadedFile $file): string;

    public function delete(string $url): void;
}

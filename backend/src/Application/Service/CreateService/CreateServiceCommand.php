<?php

namespace App\Application\Service\CreateService;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class CreateServiceCommand
{
    public function __construct(
        public string $name,
        public string $emoji,
        public string $description,
        public array  $features,
        public string $category,
        public ?UploadedFile $image = null,
    ) {}
}

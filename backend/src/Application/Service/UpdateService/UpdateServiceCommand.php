<?php

namespace App\Application\Service\UpdateService;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class UpdateServiceCommand
{
    public function __construct(
        public string $id,
        public string $name,
        /** null = no tocar el emoji que ya tenía (el panel ya no lo edita) */
        public ?string $emoji,
        public string $description,
        public array  $features,
        public string $category,
        public ?UploadedFile $image = null,
        public bool $removeImage = false,
    ) {}
}

<?php

namespace App\Application\Service\UpdatePhoto;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class UpdatePhotoCommand
{
    public function __construct(
        public string        $photoId,
        public ?string       $title,
        public ?string       $description,
        public ?string       $imageUrl,
        public ?UploadedFile $file,
    ) {}
}

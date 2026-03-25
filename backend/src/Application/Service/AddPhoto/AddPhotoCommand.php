<?php

namespace App\Application\Service\AddPhoto;

use Symfony\Component\HttpFoundation\File\UploadedFile;

final readonly class AddPhotoCommand
{
    public function __construct(
        public string        $serviceId,
        public string        $title,
        public string        $description,
        public string        $imageUrl,
        public ?UploadedFile $file,
    ) {}
}

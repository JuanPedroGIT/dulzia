<?php

namespace App\Application\Service\DeletePhoto;

final readonly class DeletePhotoCommand
{
    public function __construct(
        public string $photoId,
    ) {}
}

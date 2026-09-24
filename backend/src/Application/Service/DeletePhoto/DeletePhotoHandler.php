<?php

namespace App\Application\Service\DeletePhoto;

use App\Domain\Service\ServiceExampleRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Domain\Storage\FileStorageInterface;

final class DeletePhotoHandler
{
    public function __construct(
        private ServiceExampleRepositoryInterface $examples,
        private FileStorageInterface              $storage,
    ) {}

    public function handle(DeletePhotoCommand $command): void
    {
        $example = $this->examples->findById($command->photoId);

        if ($example === null) {
            throw new NotFoundException('Foto no encontrada');
        }

        $this->storage->delete($example->getImageUrl());

        // La miniatura es un objeto aparte: el borrado del storage no la arrastra.
        $thumbnailUrl = $example->getThumbnailUrl();
        if ($thumbnailUrl !== null) {
            $this->storage->delete($thumbnailUrl);
        }

        $this->examples->delete($example);
    }
}

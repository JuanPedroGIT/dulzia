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
        $this->examples->delete($example);
    }
}

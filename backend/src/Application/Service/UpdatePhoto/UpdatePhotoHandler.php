<?php

namespace App\Application\Service\UpdatePhoto;

use App\Domain\Service\ServiceExampleRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Domain\Storage\FileStorageInterface;

final class UpdatePhotoHandler
{
    public function __construct(
        private ServiceExampleRepositoryInterface $examples,
        private FileStorageInterface              $storage,
    ) {}

    public function handle(UpdatePhotoCommand $command): array
    {
        $example = $this->examples->findById($command->photoId);

        if ($example === null) {
            throw new NotFoundException('Foto no encontrada');
        }

        $oldImageUrl = $example->getImageUrl();
        $oldThumbnailUrl = $example->getThumbnailUrl();
        $imageUrl = $oldImageUrl;
        $thumbnailUrl = $oldThumbnailUrl;

        if ($command->file !== null) {
            $this->storage->delete($oldImageUrl);
            if ($oldThumbnailUrl !== null) {
                $this->storage->delete($oldThumbnailUrl);
            }

            $imageUrl = $this->storage->store($command->file);
            $thumbnailUrl = $command->thumbnail !== null
                ? $this->storage->store($command->thumbnail)
                : null;
        } elseif ($command->imageUrl !== null) {
            // La miniatura que hubiera era del fichero que se descarta aquí.
            if ($oldThumbnailUrl !== null) {
                $this->storage->delete($oldThumbnailUrl);
            }

            $imageUrl = $command->imageUrl;
            $thumbnailUrl = null;
        }

        $example->update(
            title:        $command->title ?? $example->getTitle(),
            description:  $command->description ?? $example->getDescription(),
            imageUrl:     $imageUrl,
            thumbnailUrl: $thumbnailUrl,
        );

        $this->examples->save($example);

        return [
            'id'          => $example->getId(),
            'title'       => $example->getTitle(),
            'description' => $example->getDescription(),
            'imageUrl'    => $example->getImageUrl(),
        ];
    }
}

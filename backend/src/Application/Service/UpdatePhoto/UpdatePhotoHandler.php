<?php

namespace App\Application\Service\UpdatePhoto;

use App\Domain\Service\ServiceExampleRepositoryInterface;
use App\Infrastructure\Storage\FileStorageInterface;

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
            throw new \DomainException('Foto no encontrada');
        }

        $imageUrl = $example->getImageUrl();

        if ($command->file !== null) {
            $this->storage->delete($imageUrl);
            $imageUrl = $this->storage->store($command->file);
        } elseif ($command->imageUrl !== null) {
            $imageUrl = $command->imageUrl;
        }

        $example->update(
            title:       $command->title ?? $example->getTitle(),
            description: $command->description ?? $example->getDescription(),
            imageUrl:    $imageUrl,
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

<?php

namespace App\Application\Service\AddPhoto;

use App\Domain\Service\ServiceExampleRepositoryInterface;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Domain\Storage\FileStorageInterface;
use App\Entity\ServiceExample;

final class AddPhotoHandler
{
    public function __construct(
        private ServiceRepositoryInterface        $services,
        private ServiceExampleRepositoryInterface $examples,
        private FileStorageInterface              $storage,
    ) {}

    public function handle(AddPhotoCommand $command): array
    {
        $service = $this->services->findById($command->serviceId);

        if ($service === null) {
            throw new NotFoundException('Servicio no encontrado');
        }

        $imageUrl = $command->file !== null
            ? $this->storage->store($command->file)
            : $command->imageUrl;

        // Solo hay miniatura cuando hay fichero: una URL externa viene sin ella.
        $thumbnailUrl = $command->file !== null && $command->thumbnail !== null
            ? $this->storage->store($command->thumbnail)
            : null;

        $example = new ServiceExample(
            service:      $service,
            title:        $command->title,
            description:  $command->description,
            imageUrl:     $imageUrl,
            thumbnailUrl: $thumbnailUrl,
            sortOrder:    $this->examples->nextSortOrderForService($command->serviceId),
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

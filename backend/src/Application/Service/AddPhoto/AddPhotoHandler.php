<?php

namespace App\Application\Service\AddPhoto;

use App\Domain\Service\ServiceExampleRepositoryInterface;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Entity\ServiceExample;
use App\Infrastructure\Storage\FileStorageInterface;

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
            throw new \DomainException('Servicio no encontrado');
        }

        $imageUrl = $command->file !== null
            ? $this->storage->store($command->file)
            : $command->imageUrl;

        $example = new ServiceExample(
            service:     $service,
            title:       $command->title,
            description: $command->description,
            imageUrl:    $imageUrl,
            sortOrder:   $this->examples->nextSortOrderForService($command->serviceId),
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

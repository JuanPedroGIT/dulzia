<?php

namespace App\Application\Service\CreateService;

use App\Application\Service\CategoryResolver;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Storage\FileStorageInterface;
use App\Entity\Service;

final class CreateServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
        private ServiceIdGenerator $idGenerator,
        private FileStorageInterface $storage,
        private CategoryResolver $categoryResolver,
    ) {}

    public function handle(CreateServiceCommand $command): array
    {
        $id = $this->idGenerator->generate($command->name);

        // Falla con 400 si la categoría no existe (no hay clave foránea).
        $category = $this->categoryResolver->resolve($command->category);

        $service = new Service(
            id:           $id,
            name:         $command->name,
            emoji:        $command->emoji,
            description:  $command->description,
            features:     $command->features,
            category:     $category->getId(),
            imageUrl:     $command->image !== null ? $this->storage->store($command->image) : null,
            thumbnailUrl: $command->image !== null && $command->thumbnail !== null
                ? $this->storage->store($command->thumbnail)
                : null,
            sortOrder:    $this->services->nextSortOrder(),
        );

        $this->services->save($service);

        return ['id' => $id, 'name' => $command->name];
    }
}

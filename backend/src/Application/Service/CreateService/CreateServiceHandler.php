<?php

namespace App\Application\Service\CreateService;

use App\Domain\Service\ServiceRepositoryInterface;
use App\Entity\Service;

final class CreateServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
        private ServiceIdGenerator $idGenerator,
    ) {}

    public function handle(CreateServiceCommand $command): array
    {
        $id = $this->idGenerator->generate($command->name);

        $service = new Service(
            id:          $id,
            name:        $command->name,
            emoji:       $command->emoji,
            description: $command->description,
            features:    $command->features,
            category:    $command->category,
            sortOrder:   $this->services->nextSortOrder(),
        );

        $this->services->save($service);

        return ['id' => $id, 'name' => $command->name];
    }
}

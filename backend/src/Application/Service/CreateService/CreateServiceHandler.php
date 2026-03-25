<?php

namespace App\Application\Service\CreateService;

use App\Domain\Service\ServiceRepositoryInterface;
use App\Entity\Service;

final class CreateServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(CreateServiceCommand $command): array
    {
        $id = $this->generateId($command->name);

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

    private function generateId(string $name): string
    {
        $id = preg_replace('/[^a-z0-9]+/', '-', strtolower($name));
        $id = trim($id, '-');

        if ($this->services->findById($id) !== null) {
            $id .= '-' . substr((string) time(), -4);
        }

        return $id;
    }
}

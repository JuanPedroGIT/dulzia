<?php

namespace App\Application\Service\UpdateService;

use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;

final class UpdateServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(UpdateServiceCommand $command): void
    {
        $service = $this->services->findById($command->id);

        if ($service === null) {
            throw new NotFoundException('Servicio no encontrado');
        }

        $service->update(
            name:        $command->name,
            emoji:       $command->emoji,
            description: $command->description,
            features:    $command->features,
            category:    $command->category,
        );

        $this->services->save($service);
    }
}

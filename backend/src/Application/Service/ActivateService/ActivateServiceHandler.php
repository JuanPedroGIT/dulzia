<?php

namespace App\Application\Service\ActivateService;

use App\Domain\Service\ServiceRepositoryInterface;

final class ActivateServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(ActivateServiceCommand $command): void
    {
        $service = $this->services->findById($command->id);

        if ($service === null) {
            throw new \DomainException('Servicio no encontrado');
        }

        $service->activate();
        $this->services->save($service);
    }
}

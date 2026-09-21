<?php

namespace App\Application\Service\DeleteService;

use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;

final class DeleteServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(DeleteServiceCommand $command): void
    {
        $service = $this->services->findById($command->id);

        if ($service === null) {
            throw new NotFoundException('Servicio no encontrado');
        }

        $service->deactivate();
        $this->services->save($service);
    }
}

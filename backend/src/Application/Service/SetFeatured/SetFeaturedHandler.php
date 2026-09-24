<?php

namespace App\Application\Service\SetFeatured;

use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;

/**
 * Marca o desmarca un servicio como destacado en la portada. Idempotente: el
 * panel manda el estado deseado, no un "alternar".
 */
final class SetFeaturedHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(SetFeaturedCommand $command): void
    {
        $service = $this->services->findById($command->id);

        if ($service === null) {
            throw new NotFoundException('Servicio no encontrado');
        }

        $service->setFeatured($command->featured);
        $this->services->save($service);
    }
}

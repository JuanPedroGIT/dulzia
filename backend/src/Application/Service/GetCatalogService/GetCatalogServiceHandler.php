<?php

namespace App\Application\Service\GetCatalogService;

use App\Domain\Service\ServiceRepositoryInterface;

/**
 * Detalle del catálogo público: solo servicios activos; null si no existe
 * o está desactivado (el controller responde 404).
 */
final class GetCatalogServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(GetCatalogServiceQuery $query): ?array
    {
        $service = $this->services->findById($query->id);

        if ($service === null || !$service->isActive()) {
            return null;
        }

        return $service->toArray();
    }
}

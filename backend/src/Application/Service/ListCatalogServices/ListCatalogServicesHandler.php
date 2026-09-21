<?php

namespace App\Application\Service\ListCatalogServices;

use App\Domain\Service\ServiceRepositoryInterface;
use App\Entity\Service;

/**
 * Catálogo público: solo servicios activos, con su representación completa.
 */
final class ListCatalogServicesHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(ListCatalogServicesQuery $query): array
    {
        return array_map(
            static fn (Service $service) => $service->toArray(),
            $this->services->findAllActive(),
        );
    }
}

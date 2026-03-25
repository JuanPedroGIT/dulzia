<?php

namespace App\Application\Service\ListServices;

use App\Domain\Service\ServiceRepositoryInterface;

final class ListServicesHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(ListServicesQuery $query): array
    {
        return array_map(
            static fn($service) => [
                'id'         => $service->getId(),
                'name'       => $service->getName(),
                'emoji'      => $service->getEmoji(),
                'category'   => $service->getCategory(),
                'sort_order' => $service->getSortOrder(),
                'is_active'  => $service->isActive(),
                'photoCount' => count($service->getExamples()),
            ],
            $this->services->findAll()
        );
    }
}

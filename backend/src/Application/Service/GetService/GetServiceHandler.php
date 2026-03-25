<?php

namespace App\Application\Service\GetService;

use App\Domain\Service\ServiceRepositoryInterface;

final class GetServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(GetServiceQuery $query): ?array
    {
        $service = $this->services->findById($query->id);

        if ($service === null) {
            return null;
        }

        return [
            'id'          => $service->getId(),
            'name'        => $service->getName(),
            'emoji'       => $service->getEmoji(),
            'description' => $service->getDescription(),
            'features'    => $service->getFeatures(),
            'category'    => $service->getCategory(),
            'sort_order'  => $service->getSortOrder(),
            'photos'      => array_map(
                static fn($e) => [
                    'id'          => $e->getId(),
                    'title'       => $e->getTitle(),
                    'description' => $e->getDescription(),
                    'imageUrl'    => $e->getImageUrl(),
                    'sort_order'  => $e->getSortOrder(),
                ],
                $service->getExamples()->toArray()
            ),
        ];
    }
}

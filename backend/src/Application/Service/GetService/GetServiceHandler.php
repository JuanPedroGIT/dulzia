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
            // imageUrl = foto propia (la que edita el panel); image = la que se
            // está mostrando en la web (propia o, si no hay, la 1ª de la galería).
            // thumbnailUrl/thumbnail siguen la misma dualidad, en versión miniatura.
            'imageUrl'     => $service->getImageUrl(),
            'thumbnailUrl' => $service->getThumbnailUrl(),
            'image'        => $service->getDisplayImage(),
            'thumbnail'    => $service->getDisplayThumbnail(),
            'photos'       => array_map(
                static fn($e) => [
                    'id'           => $e->getId(),
                    'title'        => $e->getTitle(),
                    'description'  => $e->getDescription(),
                    'imageUrl'     => $e->getImageUrl(),
                    'thumbnailUrl' => $e->getThumbnailUrl(),
                    'sort_order'   => $e->getSortOrder(),
                ],
                $service->getExamples()->toArray()
            ),
        ];
    }
}

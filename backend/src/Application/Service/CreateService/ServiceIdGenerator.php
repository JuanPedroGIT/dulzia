<?php

namespace App\Application\Service\CreateService;

use App\Application\Shared\Slug;
use App\Domain\Service\ServiceRepositoryInterface;

/**
 * Política de IDs de servicio: slug del nombre y sufijo anti-colisión.
 * Extraído del handler para mantenerlo con una única responsabilidad.
 */
final class ServiceIdGenerator
{
    public function __construct(
        private ServiceRepositoryInterface $services,
    ) {}

    public function generate(string $name): string
    {
        $id = Slug::fromName($name);

        if ($this->services->findById($id) !== null) {
            $id .= '-' . substr((string) time(), -4);
        }

        return $id;
    }
}

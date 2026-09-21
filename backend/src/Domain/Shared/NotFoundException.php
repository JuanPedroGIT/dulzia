<?php

namespace App\Domain\Shared;

/**
 * Recurso no encontrado (servicio, foto...).
 * ApiExceptionListener lo convierte en respuesta 404.
 */
final class NotFoundException extends \DomainException implements HttpMappableExceptionInterface
{
    public function getStatusCode(): int
    {
        return 404;
    }
}

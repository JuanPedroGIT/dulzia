<?php

namespace App\Domain\Shared;

/**
 * Input inválido en una petición (campos requeridos ausentes, formato...).
 * ApiExceptionListener lo convierte en respuesta 400.
 */
final class InvalidInputException extends \InvalidArgumentException implements HttpMappableExceptionInterface
{
    public function getStatusCode(): int
    {
        return 400;
    }
}

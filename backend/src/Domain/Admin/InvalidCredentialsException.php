<?php

namespace App\Domain\Admin;

use App\Domain\Shared\HttpMappableExceptionInterface;

/**
 * Credenciales de admin incorrectas.
 * ApiExceptionListener lo convierte en respuesta 401.
 */
final class InvalidCredentialsException extends \DomainException implements HttpMappableExceptionInterface
{
    public function getStatusCode(): int
    {
        return 401;
    }
}

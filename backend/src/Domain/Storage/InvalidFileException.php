<?php

namespace App\Domain\Storage;

use App\Domain\Shared\HttpMappableExceptionInterface;

/**
 * Archivo subido inválido o con MIME no permitido.
 * ApiExceptionListener lo convierte en respuesta 400.
 */
final class InvalidFileException extends \InvalidArgumentException implements HttpMappableExceptionInterface
{
    public function getStatusCode(): int
    {
        return 400;
    }
}

<?php

namespace App\Domain\Category;

use App\Domain\Shared\HttpMappableExceptionInterface;

/**
 * No se puede borrar una categoría que tienen servicios: la web se quedaría con
 * secciones apuntando a algo que ya no existe. ApiExceptionListener → 409.
 */
final class CategoryInUseException extends \DomainException implements HttpMappableExceptionInterface
{
    public static function withServiceCount(int $count): self
    {
        $sections = $count === 1 ? 'sección' : 'secciones';

        return new self(sprintf('No se puede borrar: la usan %d %s.', $count, $sections));
    }

    public function getStatusCode(): int
    {
        return 409;
    }
}

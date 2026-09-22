<?php

namespace App\Application\Contact\ListMessages;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Consulta de listado paginado de mensajes del panel admin.
 * La validación vive aquí (única fuente de verdad), como en SubmitContactCommand.
 */
final readonly class ListMessagesQuery
{
    public function __construct(
        #[Assert\GreaterThanOrEqual(1, message: 'La página debe ser mayor o igual a 1')]
        public int $page = 1,
        public int $limit = 20,
    ) {}
}

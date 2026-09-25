<?php

namespace App\Application\Contact\ListMessages;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Consulta de listado paginado de mensajes del panel admin.
 * La validación vive aquí (única fuente de verdad), como en SubmitContactCommand.
 */
final readonly class ListMessagesQuery
{
    public const ALL = 'all';
    public const UNREAD = 'unread';
    public const READ = 'read';

    public function __construct(
        #[Assert\GreaterThanOrEqual(1, message: 'La página debe ser mayor o igual a 1')]
        public int $page = 1,
        #[Assert\Choice(
            choices: [self::ALL, self::UNREAD, self::READ],
            message: 'El filtro no es válido',
        )]
        public string $filter = self::ALL,
        public int $limit = 20,
    ) {}

    /**
     * El filtro traducido a lo que entiende el repositorio: true = solo leídos,
     * false = solo sin leer, null = todos. Así el puerto no tiene que conocer
     * los nombres del filtro de la API.
     */
    public function isReadFilter(): ?bool
    {
        return match ($this->filter) {
            self::UNREAD => false,
            self::READ => true,
            default => null,
        };
    }
}

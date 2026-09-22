<?php

namespace App\Application\Contact\MarkMessageRead;

/**
 * Un solo caso de uso para leído/no leído (mismo cambio de estado,
 * en dos direcciones) — evita duplicar carpetas casi idénticas.
 */
final readonly class MarkMessageReadCommand
{
    public function __construct(
        public string $id,
        public bool $read,
    ) {}
}

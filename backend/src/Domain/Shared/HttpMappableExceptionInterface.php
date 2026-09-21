<?php

namespace App\Domain\Shared;

/**
 * Las excepciones de dominio que lo implementan se mapean solas a una
 * respuesta HTTP (status + mensaje). Así ApiExceptionListener no necesita
 * conocer cada tipo concreto: añadir una excepción nueva no toca el listener
 * (principio abierto/cerrado).
 */
interface HttpMappableExceptionInterface
{
    public function getStatusCode(): int;
}

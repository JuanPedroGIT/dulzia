<?php

namespace App\Domain\Settings;

interface SettingsRepositoryInterface
{
    /**
     * Devuelve null si la clave no existe O si su valor está vacío: así quien
     * consume puede tratar "sin configurar" y "configurado en blanco" igual,
     * y caer al valor por defecto en los dos casos.
     */
    public function get(string $key): ?string;

    /** Crea la clave o actualiza su valor. */
    public function set(string $key, string $value): void;

    /** No falla si la clave no existe, para que vaciar un ajuste sea idempotente. */
    public function delete(string $key): void;
}

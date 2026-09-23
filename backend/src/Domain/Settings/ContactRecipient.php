<?php

namespace App\Domain\Settings;

/**
 * Destinatario efectivo de las notificaciones de contacto: los valores ya
 * resueltos (panel o .env) y de dónde salió cada uno, para que el panel pueda
 * decir si manda la BD o el valor por defecto del servidor.
 */
final readonly class ContactRecipient
{
    public function __construct(
        public string $email,
        public string $name,
        public bool $emailFromSettings,
        public bool $nameFromSettings,
    ) {}
}

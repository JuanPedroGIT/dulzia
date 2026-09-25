<?php

namespace App\Domain\Settings;

/**
 * Datos de contacto que se publican en la web: los valores configurados en el
 * panel y de dónde salió cada uno. Sin configurar llegan a `null`, y es el
 * frontend el que enseña su valor por defecto: aquí no se inventa ninguno,
 * porque el que se pinta no tiene por qué ser asunto del servidor.
 */
final readonly class ContactDetails
{
    public function __construct(
        public ?string $email,
        public ?string $phone,
        public bool $emailFromSettings,
        public bool $phoneFromSettings,
    ) {}
}

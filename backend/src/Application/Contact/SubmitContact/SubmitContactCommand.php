<?php

namespace App\Application\Contact\SubmitContact;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * DTO de entrada del formulario de contacto: las reglas de validación
 * viven aquí (única fuente de verdad), no en el controller ni en la entidad.
 */
final readonly class SubmitContactCommand
{
    public function __construct(
        #[Assert\NotBlank(message: 'El nombre es obligatorio')]
        #[Assert\Length(max: 255)]
        public string $name,
        #[Assert\NotBlank(message: 'El email es obligatorio')]
        #[Assert\Email(message: 'El email no es válido')]
        public string $email,
        #[Assert\NotBlank(message: 'El mensaje es obligatorio')]
        #[Assert\Length(max: 2000, maxMessage: 'El mensaje no puede superar los 2000 caracteres')]
        public string $message,
        public ?string $phone = null,
        public ?string $eventType = null,
        public ?string $ipAddress = null,
    ) {}
}

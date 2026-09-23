<?php

namespace App\Application\Settings\UpdateContactRecipient;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Ajustes de destinatario del formulario de contacto: las reglas de validación
 * viven aquí (única fuente de verdad), no en el controller ni en la entidad.
 *
 * Vaciar un campo es un valor válido —significa "vuelve al valor por defecto
 * del .env"—, así que no hay NotBlank. El validador de Email de Symfony ya
 * ignora null y la cadena vacía, así que '' no se marca como email inválido.
 */
final readonly class UpdateContactRecipientCommand
{
    #[Assert\Email(message: 'El email no es válido')]
    #[Assert\Length(max: 255, maxMessage: 'El email no puede superar los 255 caracteres')]
    public string $email;

    #[Assert\Length(max: 150, maxMessage: 'El nombre no puede superar los 150 caracteres')]
    public string $name;

    public function __construct(string $email, string $name)
    {
        // Se recorta ANTES de validar: copiar y pegar suele arrastrar espacios,
        // y eso no debería hacer fallar un email que es correcto.
        $this->email = trim($email);
        $this->name = trim($name);
    }
}

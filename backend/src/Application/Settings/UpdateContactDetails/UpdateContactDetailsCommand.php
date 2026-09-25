<?php

namespace App\Application\Settings\UpdateContactDetails;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Datos de contacto que se publican en la web. Las reglas de validación viven
 * aquí (única fuente de verdad), no en el controller ni en la entidad.
 *
 * Vaciar un campo es un valor válido —significa "vuelve al valor por defecto de
 * la web"—, así que no hay NotBlank: los validadores de Symfony ignoran la
 * cadena vacía. El teléfono acepta el prefijo internacional opcional y los
 * separadores con los que se suele escribir (+34 629 991 659).
 */
final readonly class UpdateContactDetailsCommand
{
    #[Assert\Email(message: 'El email no es válido')]
    #[Assert\Length(max: 255, maxMessage: 'El email no puede superar los 255 caracteres')]
    public string $email;

    #[Assert\Regex(
        // De 9 a 15 dígitos con prefijo internacional opcional y los separadores
        // con los que se suele escribir (+34 629 991 659, 629-991-659, +34 (629) 991 659).
        pattern: '/^\+?\d(?:[\s.\-()]*\d){8,14}$/',
        message: 'El teléfono no es válido (por ejemplo: +34 629 991 659)',
    )]
    #[Assert\Length(max: 30, maxMessage: 'El teléfono no puede superar los 30 caracteres')]
    public string $phone;

    public function __construct(string $email, string $phone)
    {
        // Se recorta ANTES de validar: copiar y pegar suele arrastrar espacios,
        // y eso no debería hacer fallar un dato que es correcto.
        $this->email = trim($email);
        $this->phone = trim($phone);
    }
}

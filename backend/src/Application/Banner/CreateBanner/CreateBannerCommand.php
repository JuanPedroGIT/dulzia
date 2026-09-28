<?php

namespace App\Application\Banner\CreateBanner;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Aviso informativo nuevo. Las reglas de validación viven aquí (única fuente
 * de verdad), no en el controller ni en la entidad.
 *
 * Las fechas son días de calendario ("AAAA-MM-DD", ambos bordes inclusive); la
 * regla de que la ventana no puede estar al revés va en un callback a nivel de
 * clase (Assert\Expression pediría la dependencia expression-language, que no
 * compensa para una sola comparación).
 */
final readonly class CreateBannerCommand
{
    #[Assert\NotBlank(message: 'El título es obligatorio')]
    #[Assert\Length(max: 120, maxMessage: 'El título no puede superar los 120 caracteres')]
    public string $title;

    #[Assert\NotBlank(message: 'La descripción es obligatoria')]
    #[Assert\Length(max: 500, maxMessage: 'La descripción no puede superar los 500 caracteres')]
    public string $description;

    #[Assert\NotBlank(message: 'La fecha de inicio es obligatoria')]
    #[Assert\DateTime(format: 'Y-m-d', message: 'La fecha de inicio no es válida (formato: AAAA-MM-DD)')]
    public string $startsAt;

    #[Assert\NotBlank(message: 'La fecha de fin es obligatoria')]
    #[Assert\DateTime(format: 'Y-m-d', message: 'La fecha de fin no es válida (formato: AAAA-MM-DD)')]
    public string $endsAt;

    public function __construct(string $title, string $description, string $startsAt, string $endsAt)
    {
        // Se recorta ANTES de validar: copiar y pegar suele arrastrar espacios.
        $this->title = trim($title);
        $this->description = trim($description);
        $this->startsAt = trim($startsAt);
        $this->endsAt = trim($endsAt);
    }

    #[Assert\Callback]
    public function validateWindow(ExecutionContextInterface $context): void
    {
        // Solo compara cuando hay fechas que comparar: los vacíos y los formatos
        // inválidos ya los han cazado sus propias reglas.
        if ($this->startsAt !== '' && $this->endsAt !== '' && $this->endsAt < $this->startsAt) {
            $context->buildViolation('La fecha de fin no puede ser anterior a la de inicio')
                ->atPath('endsAt')
                ->addViolation();
        }
    }
}

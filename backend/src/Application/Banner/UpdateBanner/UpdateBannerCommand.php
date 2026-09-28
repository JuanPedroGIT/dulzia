<?php

namespace App\Application\Banner\UpdateBanner;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Edición de un aviso informativo. Mismas reglas que el alta (única fuente de
 * verdad en cada command); el id no se edita. La ventana al revés se valida en
 * un callback a nivel de clase, como en CreateBannerCommand.
 */
final readonly class UpdateBannerCommand
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

    public function __construct(
        public string $id,
        string $title,
        string $description,
        string $startsAt,
        string $endsAt,
    ) {
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

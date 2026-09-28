<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

// Sin constraints de validación: la frontera de validación es
// CreateBannerCommand/UpdateBannerCommand (Application) — una única fuente de verdad.
/**
 * Aviso informativo de la web: se enseña arriba del todo mientras hoy esté
 * dentro de su ventana de fechas. Puede haber varios (el de esta semana y uno
 * programado para más adelante); manda el actualizado más reciente.
 */
#[ORM\Entity]
#[ORM\Table(name: 'banner')]
class Banner
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\Column(length: 120)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $startsAt;

    #[ORM\Column(type: 'date_immutable')]
    private \DateTimeImmutable $endsAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(
        string $title,
        string $description,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
    ) {
        $this->id = bin2hex(random_bytes(16));
        $this->title = $title;
        $this->description = $description;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = $this->createdAt;
    }

    public function update(
        string $title,
        string $description,
        \DateTimeImmutable $startsAt,
        \DateTimeImmutable $endsAt,
    ): void {
        $this->title = $title;
        $this->description = $description;
        $this->startsAt = $startsAt;
        $this->endsAt = $endsAt;
        // El "activo manda" se decide por el más reciente: una edición cuenta
        // como novedad aunque solo se cambie el texto.
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getId(): string { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getStartsAt(): \DateTimeImmutable { return $this->startsAt; }
    public function getEndsAt(): \DateTimeImmutable { return $this->endsAt; }
    public function getCreatedAt(): \DateTimeImmutable { return $this->createdAt; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'starts_at'   => $this->startsAt->format('Y-m-d'),
            'ends_at'     => $this->endsAt->format('Y-m-d'),
        ];
    }
}

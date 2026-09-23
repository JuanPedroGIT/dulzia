<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

// Sin constraints de validación: la frontera de validación es
// UpdateContactRecipientCommand (Application) — una única fuente de verdad.
#[ORM\Entity]
#[ORM\Table(name: 'settings')]
class Setting
{
    // La clave es la identidad del ajuste: no hay id sustituto.
    #[ORM\Id]
    #[ORM\Column(length: 100)]
    private string $key;

    #[ORM\Column(type: 'text')]
    private string $value;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $updatedAt;

    public function __construct(string $key, string $value)
    {
        $this->key = $key;
        $this->value = $value;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getKey(): string { return $this->key; }
    public function getValue(): string { return $this->value; }
    public function getUpdatedAt(): \DateTimeImmutable { return $this->updatedAt; }

    public function setValue(string $value): void
    {
        $this->value = $value;
        $this->updatedAt = new \DateTimeImmutable();
    }
}

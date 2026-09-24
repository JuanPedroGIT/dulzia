<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Categoría de servicio (Gastronomía, Decoración...). Se gestiona desde el panel
 * y el identificador es estable: los servicios lo referencian por clave foránea.
 */
#[ORM\Entity]
#[ORM\Table(name: 'category')]
class Category
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 50)]
    private string $id;

    #[ORM\Column(length: 100)]
    private string $name;

    /** Adorno de las pestañas del catálogo (🍴, 🎨...). Null = sin emoji. */
    #[ORM\Column(length: 20, nullable: true)]
    private ?string $emoji = null;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    public function __construct(
        string $id,
        string $name,
        ?string $emoji = null,
        int $sortOrder = 0,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->emoji = $emoji;
        $this->sortOrder = $sortOrder;
    }

    /** El identificador no se edita: los servicios lo referencian. */
    public function update(string $name, ?string $emoji, int $sortOrder): void
    {
        $this->name = $name;
        $this->emoji = $emoji;
        $this->sortOrder = $sortOrder;
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmoji(): ?string { return $this->emoji; }
    public function getSortOrder(): int { return $this->sortOrder; }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->name,
            'emoji'      => $this->emoji,
            'sort_order' => $this->sortOrder,
        ];
    }
}

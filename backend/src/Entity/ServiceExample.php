<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'service_example')]
class ServiceExample
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36)]
    private string $id;

    #[ORM\ManyToOne(targetEntity: Service::class, inversedBy: 'examples')]
    #[ORM\JoinColumn(name: 'service_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Service $service;

    #[ORM\Column(length: 255)]
    private string $title;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(length: 500)]
    private string $imageUrl;

    /**
     * Miniatura de image_url, la sube el navegador junto a la foto. Null = no hay
     * miniatura (fotos subidas antes de existir, o URL externa): se usa la grande.
     */
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $thumbnailUrl = null;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    public function __construct(
        Service $service,
        string $title,
        string $description,
        string $imageUrl,
        ?string $thumbnailUrl = null,
        int $sortOrder = 0,
    ) {
        $this->id = bin2hex(random_bytes(16));
        $this->service = $service;
        $this->title = $title;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
        $this->thumbnailUrl = $thumbnailUrl;
        $this->sortOrder = $sortOrder;
    }

    public function update(string $title, string $description, string $imageUrl, ?string $thumbnailUrl): void
    {
        $this->title = $title;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
        $this->thumbnailUrl = $thumbnailUrl;
    }

    public function getId(): string { return $this->id; }
    public function getService(): Service { return $this->service; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getImageUrl(): string { return $this->imageUrl; }
    public function getThumbnailUrl(): ?string { return $this->thumbnailUrl; }
    public function getSortOrder(): int { return $this->sortOrder; }

    /**
     * Miniatura que se sirve en las listas: la suya y, si no hay, la foto grande.
     * Nunca es null mientras haya imagen, así el frontend no tiene que decidir.
     */
    public function getDisplayThumbnail(): string
    {
        return $this->thumbnailUrl ?? $this->imageUrl;
    }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'image'       => $this->imageUrl,
            'thumbnail'   => $this->getDisplayThumbnail(),
        ];
    }
}

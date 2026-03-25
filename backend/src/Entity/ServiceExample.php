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

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    public function __construct(
        Service $service,
        string $title,
        string $description,
        string $imageUrl,
        int $sortOrder = 0,
    ) {
        $this->id = bin2hex(random_bytes(16));
        $this->service = $service;
        $this->title = $title;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
        $this->sortOrder = $sortOrder;
    }

    public function update(string $title, string $description, string $imageUrl): void
    {
        $this->title = $title;
        $this->description = $description;
        $this->imageUrl = $imageUrl;
    }

    public function getId(): string { return $this->id; }
    public function getService(): Service { return $this->service; }
    public function getTitle(): string { return $this->title; }
    public function getDescription(): string { return $this->description; }
    public function getImageUrl(): string { return $this->imageUrl; }
    public function getSortOrder(): int { return $this->sortOrder; }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'title'       => $this->title,
            'description' => $this->description,
            'image'       => $this->imageUrl,
        ];
    }
}

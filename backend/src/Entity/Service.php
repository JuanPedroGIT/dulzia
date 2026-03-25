<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'service')]
class Service
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 100)]
    private string $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 20)]
    private string $emoji;

    #[ORM\Column(type: 'text')]
    private string $description;

    #[ORM\Column(type: 'json')]
    private array $features = [];

    #[ORM\Column(length: 50)]
    private string $category;

    #[ORM\Column(type: 'integer')]
    private int $sortOrder = 0;

    #[ORM\Column(type: 'boolean')]
    private bool $isActive = true;

    #[ORM\OneToMany(targetEntity: ServiceExample::class, mappedBy: 'service', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['sortOrder' => 'ASC'])]
    private Collection $examples;

    public function __construct(
        string $id,
        string $name,
        string $emoji,
        string $description,
        array $features,
        string $category,
        int $sortOrder = 0,
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->emoji = $emoji;
        $this->description = $description;
        $this->features = $features;
        $this->category = $category;
        $this->sortOrder = $sortOrder;
        $this->examples = new ArrayCollection();
    }

    public function update(string $name, string $emoji, string $description, array $features, string $category): void
    {
        $this->name = $name;
        $this->emoji = $emoji;
        $this->description = $description;
        $this->features = $features;
        $this->category = $category;
    }

    public function deactivate(): void
    {
        $this->isActive = false;
    }

    public function activate(): void
    {
        $this->isActive = true;
    }

    public function getId(): string { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getEmoji(): string { return $this->emoji; }
    public function getDescription(): string { return $this->description; }
    public function getFeatures(): array { return $this->features; }
    public function getCategory(): string { return $this->category; }
    public function getSortOrder(): int { return $this->sortOrder; }
    public function isActive(): bool { return $this->isActive; }

    /** @return Collection<int, ServiceExample> */
    public function getExamples(): Collection { return $this->examples; }

    public function toArray(): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'emoji'       => $this->emoji,
            'description' => $this->description,
            'features'    => $this->features,
            'category'    => $this->category,
            'examples'    => $this->examples->map(fn(ServiceExample $e) => $e->toArray())->toArray(),
        ];
    }
}

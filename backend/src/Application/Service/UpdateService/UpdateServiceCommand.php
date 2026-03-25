<?php

namespace App\Application\Service\UpdateService;

final readonly class UpdateServiceCommand
{
    public function __construct(
        public string $id,
        public string $name,
        public string $emoji,
        public string $description,
        public array  $features,
        public string $category,
    ) {}
}

<?php

namespace App\Application\Category\UpdateCategory;

final readonly class UpdateCategoryCommand
{
    public function __construct(
        public string  $id,
        public string  $name,
        public ?string $emoji,
        public int     $sortOrder,
    ) {}
}

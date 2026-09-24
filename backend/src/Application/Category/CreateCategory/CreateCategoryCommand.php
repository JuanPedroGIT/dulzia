<?php

namespace App\Application\Category\CreateCategory;

final readonly class CreateCategoryCommand
{
    public function __construct(
        public string  $name,
        public ?string $emoji,
        public int     $sortOrder,
    ) {}
}

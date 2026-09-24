<?php

namespace App\Application\Category\DeleteCategory;

final readonly class DeleteCategoryCommand
{
    public function __construct(
        public string $id,
    ) {}
}

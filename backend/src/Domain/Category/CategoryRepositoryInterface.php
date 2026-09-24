<?php

namespace App\Domain\Category;

use App\Entity\Category;

interface CategoryRepositoryInterface
{
    /** @return Category[] Ordenadas por sort_order. */
    public function findAll(): array;

    public function findById(string $id): ?Category;

    public function save(Category $category): void;

    public function delete(Category $category): void;
}

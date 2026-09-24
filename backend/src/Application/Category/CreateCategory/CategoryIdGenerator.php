<?php

namespace App\Application\Category\CreateCategory;

use App\Application\Shared\Slug;
use App\Domain\Category\CategoryRepositoryInterface;

/**
 * Política de IDs de categoría: slug del nombre y sufijo anti-colisión. Mismo
 * criterio que ServiceIdGenerator, pero sobre el repositorio de categorías.
 */
final class CategoryIdGenerator
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
    ) {}

    public function generate(string $name): string
    {
        $id = Slug::fromName($name);

        if ($this->categories->findById($id) !== null) {
            $id .= '-' . substr((string) time(), -4);
        }

        return $id;
    }
}

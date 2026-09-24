<?php

namespace App\Application\Service;

use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Shared\InvalidInputException;
use App\Entity\Category;

/**
 * Comprueba que la categoría de un servicio existe. Como no hay clave foránea
 * (ver la migración de `category`), esta es la puerta que impide guardar una
 * sección con una categoría inventada.
 */
final class CategoryResolver
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
    ) {}

    public function resolve(string $categoryId): Category
    {
        $category = $this->categories->findById($categoryId);

        if ($category === null) {
            throw new InvalidInputException(sprintf('La categoría "%s" no existe', $categoryId));
        }

        return $category;
    }
}

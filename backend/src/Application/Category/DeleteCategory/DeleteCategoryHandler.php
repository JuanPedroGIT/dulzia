<?php

namespace App\Application\Category\DeleteCategory;

use App\Domain\Category\CategoryInUseException;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;

final class DeleteCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private ServiceRepositoryInterface $services,
    ) {}

    public function handle(DeleteCategoryCommand $command): void
    {
        $category = $this->categories->findById($command->id);

        if ($category === null) {
            throw new NotFoundException('Categoría no encontrada');
        }

        // La web no puede quedarse con secciones apuntando a una categoría que ya
        // no existe: se avisa con cuántas son para que se muevan primero.
        $inUse = $this->services->countByCategory($category->getId());

        if ($inUse > 0) {
            throw CategoryInUseException::withServiceCount($inUse);
        }

        $this->categories->delete($category);
    }
}

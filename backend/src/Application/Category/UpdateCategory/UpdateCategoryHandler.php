<?php

namespace App\Application\Category\UpdateCategory;

use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Shared\NotFoundException;

final class UpdateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
    ) {}

    public function handle(UpdateCategoryCommand $command): void
    {
        $category = $this->categories->findById($command->id);

        if ($category === null) {
            throw new NotFoundException('Categoría no encontrada');
        }

        // El identificador no se toca: los servicios lo referencian.
        $category->update(
            name: $command->name,
            emoji: $command->emoji,
            sortOrder: $command->sortOrder,
        );

        $this->categories->save($category);
    }
}

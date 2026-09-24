<?php

namespace App\Application\Category\CreateCategory;

use App\Domain\Category\CategoryRepositoryInterface;
use App\Entity\Category;

final class CreateCategoryHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private CategoryIdGenerator $idGenerator,
    ) {}

    public function handle(CreateCategoryCommand $command): array
    {
        $category = new Category(
            id: $this->idGenerator->generate($command->name),
            name: $command->name,
            emoji: $command->emoji,
            sortOrder: $command->sortOrder,
        );

        $this->categories->save($category);

        return $category->toArray();
    }
}

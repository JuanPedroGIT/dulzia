<?php

namespace App\Application\Category\ListCategories;

use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Entity\Category;

final class ListCategoriesHandler
{
    public function __construct(
        private CategoryRepositoryInterface $categories,
        private ServiceRepositoryInterface $services,
    ) {}

    /** @return array<int, array> */
    public function handle(ListCategoriesQuery $query): array
    {
        return array_map(
            function (Category $category) use ($query): array {
                $data = $category->toArray();

                if ($query->withServiceCount) {
                    $data['serviceCount'] = $this->services->countByCategory($category->getId());
                }

                return $data;
            },
            $this->categories->findAll(),
        );
    }
}

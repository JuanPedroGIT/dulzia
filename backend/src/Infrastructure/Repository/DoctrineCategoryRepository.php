<?php

namespace App\Infrastructure\Repository;

use App\Domain\Category\CategoryRepositoryInterface;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineCategoryRepository implements CategoryRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function findAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('c')
            ->from(Category::class, 'c')
            ->orderBy('c.sortOrder', 'ASC')
            ->addOrderBy('c.name', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findById(string $id): ?Category
    {
        return $this->em->getRepository(Category::class)->find($id);
    }

    public function save(Category $category): void
    {
        $this->em->persist($category);
        $this->em->flush();
    }

    public function delete(Category $category): void
    {
        $this->em->remove($category);
        $this->em->flush();
    }
}

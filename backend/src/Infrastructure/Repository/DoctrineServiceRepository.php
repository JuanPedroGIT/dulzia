<?php

namespace App\Infrastructure\Repository;

use App\Domain\Service\ServiceRepositoryInterface;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineServiceRepository implements ServiceRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function findAllActive(): array
    {
        return $this->em->createQueryBuilder()
            ->select('s')
            ->from(Service::class, 's')
            ->where('s.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('s.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('s')
            ->from(Service::class, 's')
            ->orderBy('s.sortOrder', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findById(string $id): ?Service
    {
        return $this->em->getRepository(Service::class)->find($id);
    }

    public function save(Service $service): void
    {
        $this->em->persist($service);
        $this->em->flush();
    }

    public function delete(Service $service): void
    {
        $this->em->remove($service);
        $this->em->flush();
    }

    public function nextSortOrder(): int
    {
        $max = $this->em->createQuery(
            'SELECT COALESCE(MAX(s.sortOrder), 0) FROM App\Entity\Service s'
        )->getSingleScalarResult();

        return (int) $max + 1;
    }
}

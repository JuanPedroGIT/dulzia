<?php

namespace App\Infrastructure\Repository;

use App\Domain\Service\ServiceExampleRepositoryInterface;
use App\Entity\ServiceExample;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineServiceExampleRepository implements ServiceExampleRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function findById(string $id): ?ServiceExample
    {
        return $this->em->getRepository(ServiceExample::class)->find($id);
    }

    public function save(ServiceExample $example): void
    {
        $this->em->persist($example);
        $this->em->flush();
    }

    public function delete(ServiceExample $example): void
    {
        $this->em->remove($example);
        $this->em->flush();
    }

    public function nextSortOrderForService(string $serviceId): int
    {
        $max = $this->em->createQuery(
            'SELECT COALESCE(MAX(e.sortOrder), 0) FROM App\Entity\ServiceExample e WHERE e.service = :serviceId'
        )->setParameter('serviceId', $serviceId)->getSingleScalarResult();

        return (int) $max + 1;
    }
}

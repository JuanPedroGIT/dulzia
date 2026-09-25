<?php

namespace App\Infrastructure\Repository;

use App\Domain\Contact\ContactRepositoryInterface;
use App\Entity\ContactSubmission;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineContactRepository implements ContactRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function save(ContactSubmission $submission): void
    {
        $this->em->persist($submission);
        $this->em->flush();
    }

    public function findPage(int $offset, int $limit, ?bool $isRead = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('c')
            ->from(ContactSubmission::class, 'c')
            ->orderBy('c.submittedAt', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        if ($isRead !== null) {
            $qb->andWhere($isRead ? 'c.readAt IS NOT NULL' : 'c.readAt IS NULL');
        }

        return $qb->getQuery()->getResult();
    }

    public function find(string $id): ?ContactSubmission
    {
        return $this->em->getRepository(ContactSubmission::class)->find($id);
    }

    public function countAll(): int
    {
        return (int) $this->em->createQuery(
            'SELECT COUNT(c.id) FROM App\Entity\ContactSubmission c'
        )->getSingleScalarResult();
    }

    public function countUnread(): int
    {
        return (int) $this->em->createQuery(
            'SELECT COUNT(c.id) FROM App\Entity\ContactSubmission c WHERE c.readAt IS NULL'
        )->getSingleScalarResult();
    }

    public function remove(ContactSubmission $submission): void
    {
        $this->em->remove($submission);
        $this->em->flush();
    }
}

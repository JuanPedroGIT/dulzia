<?php

namespace App\Infrastructure\Repository;

use App\Domain\Banner\BannerRepositoryInterface;
use App\Entity\Banner;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineBannerRepository implements BannerRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function save(Banner $banner): void
    {
        $this->em->persist($banner);
        $this->em->flush();
    }

    public function delete(Banner $banner): void
    {
        $this->em->remove($banner);
        $this->em->flush();
    }

    public function find(string $id): ?Banner
    {
        return $this->em->getRepository(Banner::class)->find($id);
    }

    public function findAll(): array
    {
        return $this->em->createQueryBuilder()
            ->select('b')
            ->from(Banner::class, 'b')
            ->orderBy('b.updatedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveOn(\DateTimeImmutable $date): ?Banner
    {
        return $this->em->createQueryBuilder()
            ->select('b')
            ->from(Banner::class, 'b')
            ->where('b.startsAt <= :date')
            ->andWhere('b.endsAt >= :date')
            ->setParameter('date', $date)
            ->orderBy('b.updatedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}

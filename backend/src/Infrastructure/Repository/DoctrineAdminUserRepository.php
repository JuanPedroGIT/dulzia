<?php

namespace App\Infrastructure\Repository;

use App\Domain\Admin\AdminUserRepositoryInterface;
use App\Entity\AdminUser;
use Doctrine\ORM\EntityManagerInterface;

final class DoctrineAdminUserRepository implements AdminUserRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $em,
    ) {}

    public function findPasswordHash(string $username): ?string
    {
        $admin = $this->em->getRepository(AdminUser::class)->findOneBy(['username' => $username]);

        return $admin?->getPasswordHash();
    }
}

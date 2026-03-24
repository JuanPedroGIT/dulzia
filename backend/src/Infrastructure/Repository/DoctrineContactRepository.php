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
}

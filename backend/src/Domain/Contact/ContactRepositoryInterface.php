<?php

namespace App\Domain\Contact;

use App\Entity\ContactSubmission;

interface ContactRepositoryInterface
{
    public function save(ContactSubmission $submission): void;

    /** @return ContactSubmission[] */
    public function findPage(int $offset, int $limit): array;

    public function find(string $id): ?ContactSubmission;

    public function countAll(): int;

    public function countUnread(): int;

    public function remove(ContactSubmission $submission): void;
}

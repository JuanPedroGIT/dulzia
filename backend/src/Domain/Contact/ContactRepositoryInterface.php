<?php

namespace App\Domain\Contact;

use App\Entity\ContactSubmission;

interface ContactRepositoryInterface
{
    public function save(ContactSubmission $submission): void;

    /**
     * Página de mensajes, del más reciente al más antiguo.
     *
     * @param bool|null $isRead true = solo leídos, false = solo sin leer, null = todos
     *
     * @return ContactSubmission[]
     */
    public function findPage(int $offset, int $limit, ?bool $isRead = null): array;

    public function find(string $id): ?ContactSubmission;

    public function countAll(): int;

    public function countUnread(): int;

    public function remove(ContactSubmission $submission): void;
}

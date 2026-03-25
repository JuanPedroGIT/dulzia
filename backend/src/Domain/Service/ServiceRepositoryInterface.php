<?php

namespace App\Domain\Service;

use App\Entity\Service;

interface ServiceRepositoryInterface
{
    /** @return Service[] */
    public function findAllActive(): array;

    /** @return Service[] */
    public function findAll(): array;

    public function findById(string $id): ?Service;

    public function save(Service $service): void;

    public function delete(Service $service): void;

    public function nextSortOrder(): int;
}

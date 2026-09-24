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

    /** Cuántos servicios usan una categoría (para bloquear su borrado). */
    public function countByCategory(string $categoryId): int;

    public function save(Service $service): void;

    public function nextSortOrder(): int;
}

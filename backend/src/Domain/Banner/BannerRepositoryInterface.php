<?php

namespace App\Domain\Banner;

use App\Entity\Banner;

interface BannerRepositoryInterface
{
    public function save(Banner $banner): void;

    public function delete(Banner $banner): void;

    public function find(string $id): ?Banner;

    /** @return Banner[] Todos, de más reciente a más antiguo. */
    public function findAll(): array;

    /**
     * El banner visible en la fecha dada (ambos bordes inclusive). Si hay
     * varios en ventana, el actualizado más reciente.
     */
    public function findActiveOn(\DateTimeImmutable $date): ?Banner;
}

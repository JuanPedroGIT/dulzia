<?php

namespace App\Domain\Service;

use App\Entity\ServiceExample;

interface ServiceExampleRepositoryInterface
{
    public function findById(string $id): ?ServiceExample;

    public function save(ServiceExample $example): void;

    public function delete(ServiceExample $example): void;

    public function nextSortOrderForService(string $serviceId): int;
}

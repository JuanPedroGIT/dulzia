<?php

namespace App\Domain\Admin;

interface AdminUserRepositoryInterface
{
    public function findPasswordHash(string $username): ?string;
}

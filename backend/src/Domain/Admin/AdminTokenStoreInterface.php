<?php

namespace App\Domain\Admin;

interface AdminTokenStoreInterface
{
    /** Crea un token nuevo (invalidando los anteriores) y lo devuelve. */
    public function create(): string;

    public function isValid(string $token): bool;

    public function deleteAll(): void;
}

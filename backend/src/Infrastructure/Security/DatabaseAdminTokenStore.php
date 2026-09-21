<?php

namespace App\Infrastructure\Security;

use App\Domain\Admin\AdminTokenStoreInterface;
use Doctrine\DBAL\Connection;

/**
 * Tokens de admin en la tabla admin_token (un solo token válido a la vez).
 * Sustituye al antiguo App\Security\AdminTokenService (acoplado a Request).
 */
final class DatabaseAdminTokenStore implements AdminTokenStoreInterface
{
    public function __construct(private Connection $connection) {}

    public function create(): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTime('+8 hours'))->format('Y-m-d H:i:s');

        $this->connection->executeStatement('DELETE FROM admin_token');
        $this->connection->executeStatement(
            'INSERT INTO admin_token (token, expires_at) VALUES (?, ?)',
            [$token, $expiresAt],
        );

        return $token;
    }

    public function isValid(string $token): bool
    {
        $row = $this->connection->fetchAssociative(
            'SELECT 1 FROM admin_token WHERE token = ? AND expires_at > NOW()',
            [$token],
        );

        return $row !== false;
    }

    public function deleteAll(): void
    {
        $this->connection->executeStatement('DELETE FROM admin_token');
    }
}

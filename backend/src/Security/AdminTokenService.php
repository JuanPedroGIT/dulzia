<?php
namespace App\Security;

use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\Request;

class AdminTokenService
{
    public function __construct(private Connection $connection) {}

    public function createToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $expiresAt = (new \DateTime('+8 hours'))->format('Y-m-d H:i:s');

        $this->connection->executeStatement('DELETE FROM admin_token');
        $this->connection->executeStatement(
            'INSERT INTO admin_token (token, expires_at) VALUES (?, ?)',
            [$token, $expiresAt]
        );

        return $token;
    }

    public function validateRequest(Request $request): void
    {
        $authHeader = $request->headers->get('Authorization', '');
        if (!str_starts_with($authHeader, 'Bearer ')) {
            throw new \RuntimeException('Unauthorized', 401);
        }
        $token = substr($authHeader, 7);
        $row = $this->connection->fetchAssociative(
            "SELECT token FROM admin_token WHERE token = ? AND expires_at > NOW()",
            [$token]
        );
        if (!$row) {
            throw new \RuntimeException('Unauthorized', 401);
        }
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Infrastructure\Security;

use App\Infrastructure\Security\DatabaseAdminTokenStore;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\TestCase;

final class DatabaseAdminTokenStoreTest extends TestCase
{
    public function testCreateDeletesPreviousAndInsertsNew(): void
    {
        $connection = $this->createMock(Connection::class);

        $captured = [];
        $connection->expects($this->exactly(2))
            ->method('executeStatement')
            ->willReturnCallback(function (string $sql, array $params = []) use (&$captured): int {
                $captured[] = [$sql, $params];

                return 1;
            });

        $token = (new DatabaseAdminTokenStore($connection))->create();

        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $token);

        self::assertSame('DELETE FROM admin_token', $captured[0][0]);
        self::assertSame('INSERT INTO admin_token (token, expires_at) VALUES (?, ?)', $captured[1][0]);
        self::assertSame($token, $captured[1][1][0]);
        self::assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/',
            $captured[1][1][1],
        );

        // Expiración ≈ +8 horas
        $expiresAt = \DateTime::createFromFormat('Y-m-d H:i:s', $captured[1][1][1]);
        $diffSeconds = $expiresAt->getTimestamp() - time();
        self::assertGreaterThan(7 * 3600, $diffSeconds);
        self::assertLessThan(9 * 3600, $diffSeconds);
    }

    public function testIsValidReturnsTrueForExistingToken(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('fetchAssociative')
            ->with('SELECT 1 FROM admin_token WHERE token = ? AND expires_at > NOW()', ['abc123'])
            ->willReturn(['?column?' => 1]);

        self::assertTrue((new DatabaseAdminTokenStore($connection))->isValid('abc123'));
    }

    public function testIsValidReturnsFalseForUnknownOrExpiredToken(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->method('fetchAssociative')->willReturn(false);

        self::assertFalse((new DatabaseAdminTokenStore($connection))->isValid('desconocido'));
    }

    public function testDeleteAllClearsTokens(): void
    {
        $connection = $this->createMock(Connection::class);
        $connection->expects($this->once())
            ->method('executeStatement')
            ->with('DELETE FROM admin_token');

        (new DatabaseAdminTokenStore($connection))->deleteAll();
    }
}

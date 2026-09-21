<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\AdminToken;
use PHPUnit\Framework\TestCase;

final class AdminTokenTest extends TestCase
{
    public function testIsExpiredWithPastAndFutureDates(): void
    {
        $expired = new AdminToken('abc', new \DateTime('-1 hour'));
        self::assertTrue($expired->isExpired());

        $valid = new AdminToken('abc', new \DateTime('+1 hour'));
        self::assertFalse($valid->isExpired());
    }

    public function testGetters(): void
    {
        $expiresAt = new \DateTime('+8 hours');
        $token = new AdminToken('abc123', $expiresAt);

        self::assertNull($token->getId());
        self::assertSame('abc123', $token->getToken());
        self::assertSame($expiresAt, $token->getExpiresAt());
    }
}

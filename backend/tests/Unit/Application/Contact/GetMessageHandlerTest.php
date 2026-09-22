<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Contact;

use App\Application\Contact\GetMessage\GetMessageHandler;
use App\Application\Contact\GetMessage\GetMessageQuery;
use App\Domain\Contact\ContactRepositoryInterface;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class GetMessageHandlerTest extends TestCase
{
    public function testReturnsFullMessageData(): void
    {
        $sub = TestFactory::contactSubmission(name: 'María', ipAddress: '127.0.0.1');

        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->expects($this->once())->method('find')->with('abc')->willReturn($sub);

        $result = (new GetMessageHandler($repository))->handle(new GetMessageQuery('abc'));

        self::assertSame($sub->getId(), $result['id']);
        self::assertSame('María', $result['name']);
        self::assertSame('maria@example.com', $result['email']);
        self::assertSame('Hola, quiero un presupuesto', $result['message']);
        self::assertSame('127.0.0.1', $result['ip_address']);
        self::assertSame('boda', $result['event_type']);
        self::assertFalse($result['is_read']);
        self::assertNull($result['read_at']);
        self::assertFalse($result['email_sent']);
    }

    public function testReturnsNullWhenNotFound(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->method('find')->willReturn(null);

        self::assertNull((new GetMessageHandler($repository))->handle(new GetMessageQuery('nope')));
    }
}

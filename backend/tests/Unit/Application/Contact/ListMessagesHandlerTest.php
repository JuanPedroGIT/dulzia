<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Contact;

use App\Application\Contact\ListMessages\ListMessagesHandler;
use App\Application\Contact\ListMessages\ListMessagesQuery;
use App\Domain\Contact\ContactRepositoryInterface;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class ListMessagesHandlerTest extends TestCase
{
    public function testReturnsItemsWithPaginationAndCounts(): void
    {
        $sub1 = TestFactory::contactSubmission(name: 'María');
        $sub2 = TestFactory::contactSubmission(name: 'Juan');

        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findPage')
            ->with(20, 20)
            ->willReturn([$sub1, $sub2]);
        $repository->method('countAll')->willReturn(25);
        $repository->method('countUnread')->willReturn(3);

        $result = (new ListMessagesHandler($repository))->handle(new ListMessagesQuery(page: 2));

        self::assertCount(2, $result['items']);
        self::assertSame('María', $result['items'][0]['name']);
        self::assertSame('maria@example.com', $result['items'][0]['email']);
        self::assertSame('boda', $result['items'][0]['event_type']);
        self::assertFalse($result['items'][0]['is_read']);
        self::assertFalse($result['items'][0]['email_sent']);
        self::assertNull($result['items'][0]['email_sent_at']);
        self::assertIsString($result['items'][0]['submitted_at']);

        self::assertSame(2, $result['page']);
        self::assertSame(20, $result['limit']);
        self::assertSame(2, $result['totalPages']);
        self::assertSame(25, $result['total']);
        self::assertSame(3, $result['unreadCount']);
    }

    public function testFirstPageStartsAtOffsetZero(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findPage')
            ->with(0, 20)
            ->willReturn([]);
        $repository->method('countAll')->willReturn(0);
        $repository->method('countUnread')->willReturn(0);

        $result = (new ListMessagesHandler($repository))->handle(new ListMessagesQuery());

        self::assertSame([], $result['items']);
        self::assertSame(1, $result['totalPages']); // nunca 0 páginas
        self::assertSame(0, $result['total']);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Contact;

use App\Application\Contact\MarkMessageRead\MarkMessageReadCommand;
use App\Application\Contact\MarkMessageRead\MarkMessageReadHandler;
use App\Domain\Contact\ContactRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class MarkMessageReadHandlerTest extends TestCase
{
    public function testMarksAsRead(): void
    {
        $sub = TestFactory::contactSubmission();

        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->method('find')->willReturn($sub);
        $repository->expects($this->once())->method('save')->with($sub);

        (new MarkMessageReadHandler($repository))->handle(new MarkMessageReadCommand('abc', true));

        self::assertTrue($sub->isRead());
    }

    public function testMarksAsUnread(): void
    {
        $sub = TestFactory::contactSubmission();
        $sub->markRead();

        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->method('find')->willReturn($sub);
        $repository->expects($this->once())->method('save')->with($sub);

        (new MarkMessageReadHandler($repository))->handle(new MarkMessageReadCommand('abc', false));

        self::assertFalse($sub->isRead());
    }

    public function testThrowsNotFoundWhenMessageDoesNotExist(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->method('find')->willReturn(null);
        $repository->expects($this->never())->method('save');

        $this->expectException(NotFoundException::class);

        (new MarkMessageReadHandler($repository))->handle(new MarkMessageReadCommand('nope', true));
    }
}

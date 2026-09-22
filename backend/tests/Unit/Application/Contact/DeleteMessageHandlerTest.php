<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Contact;

use App\Application\Contact\DeleteMessage\DeleteMessageCommand;
use App\Application\Contact\DeleteMessage\DeleteMessageHandler;
use App\Domain\Contact\ContactRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class DeleteMessageHandlerTest extends TestCase
{
    public function testRemovesMessage(): void
    {
        $sub = TestFactory::contactSubmission();

        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->method('find')->willReturn($sub);
        $repository->expects($this->once())->method('remove')->with($sub);

        (new DeleteMessageHandler($repository))->handle(new DeleteMessageCommand('abc'));
    }

    public function testThrowsNotFoundWhenMessageDoesNotExist(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->method('find')->willReturn(null);
        $repository->expects($this->never())->method('remove');

        $this->expectException(NotFoundException::class);

        (new DeleteMessageHandler($repository))->handle(new DeleteMessageCommand('nope'));
    }
}

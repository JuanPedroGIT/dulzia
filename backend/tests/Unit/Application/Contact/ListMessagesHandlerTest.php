<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Contact;

use App\Application\Contact\ListMessages\ListMessagesHandler;
use App\Application\Contact\ListMessages\ListMessagesQuery;
use App\Domain\Contact\ContactRepositoryInterface;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class ListMessagesHandlerTest extends TestCase
{
    public function testReturnsItemsWithPaginationAndCounts(): void
    {
        $sub1 = TestFactory::contactSubmission(name: 'María');
        $sub2 = TestFactory::contactSubmission(name: 'Juan');

        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findPage')
            // Sin filtro el repositorio recibe null: no hay nada que filtrar.
            ->with(20, 20, null)
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
        self::assertSame(ListMessagesQuery::ALL, $result['filter']);
        // Los tres contadores de las pestañas; los leídos salen de restar.
        self::assertSame(['all' => 25, 'unread' => 3, 'read' => 22], $result['counts']);
    }

    public function testFirstPageStartsAtOffsetZero(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findPage')
            ->with(0, 20, null)
            ->willReturn([]);
        $repository->method('countAll')->willReturn(0);
        $repository->method('countUnread')->willReturn(0);

        $result = (new ListMessagesHandler($repository))->handle(new ListMessagesQuery());

        self::assertSame([], $result['items']);
        self::assertSame(1, $result['totalPages']); // nunca 0 páginas
        self::assertSame(0, $result['total']);
    }

    public function testUnreadFilterPagesOnlyUnreadMessages(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findPage')
            ->with(0, 20, false)
            ->willReturn([]);
        $repository->method('countAll')->willReturn(25);
        $repository->method('countUnread')->willReturn(3);

        $result = (new ListMessagesHandler($repository))->handle(
            new ListMessagesQuery(filter: ListMessagesQuery::UNREAD),
        );

        // El total que pagina la tabla es el del filtro, no el de todos…
        self::assertSame(3, $result['total']);
        self::assertSame(1, $result['totalPages']);
        // …pero los contadores siguen siendo globales, para las pestañas.
        self::assertSame(['all' => 25, 'unread' => 3, 'read' => 22], $result['counts']);
    }

    public function testReadFilterPagesOnlyReadMessages(): void
    {
        $repository = $this->createMock(ContactRepositoryInterface::class);
        $repository->expects($this->once())
            ->method('findPage')
            ->with(0, 20, true)
            ->willReturn([]);
        $repository->method('countAll')->willReturn(25);
        $repository->method('countUnread')->willReturn(3);

        $result = (new ListMessagesHandler($repository))->handle(
            new ListMessagesQuery(filter: ListMessagesQuery::READ),
        );

        self::assertSame(22, $result['total']);
        self::assertSame(2, $result['totalPages']);
    }

    public function testAnInventedFilterIsRejected(): void
    {
        $validator = Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator();

        $violations = $validator->validate(new ListMessagesQuery(filter: 'inventado'));

        self::assertCount(1, $violations);
        self::assertStringContainsString('El filtro no es válido', (string) $violations[0]->getMessage());
    }
}

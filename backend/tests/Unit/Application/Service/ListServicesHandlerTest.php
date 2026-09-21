<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\ListServices\ListServicesHandler;
use App\Application\Service\ListServices\ListServicesQuery;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class ListServicesHandlerTest extends TestCase
{
    public function testMapsAllServicesIncludingInactive(): void
    {
        $active = TestFactory::service(id: 'a', name: 'Activo', sortOrder: 1);
        TestFactory::attachExamples(
            $active,
            TestFactory::example($active),
            TestFactory::example($active, title: 'Foto 2'),
        );

        $inactive = TestFactory::service(id: 'b', name: 'Inactivo', sortOrder: 2, isActive: false);

        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findAll')->willReturn([$active, $inactive]);

        $result = (new ListServicesHandler($repo))->handle(new ListServicesQuery());

        self::assertCount(2, $result);
        self::assertSame('a', $result[0]['id']);
        self::assertSame('Activo', $result[0]['name']);
        self::assertSame(1, $result[0]['sort_order']);
        self::assertTrue($result[0]['is_active']);
        self::assertSame(2, $result[0]['photoCount']);

        self::assertSame('b', $result[1]['id']);
        self::assertFalse($result[1]['is_active']);
        self::assertSame(0, $result[1]['photoCount']);
    }

    public function testReturnsEmptyArrayWhenNoServices(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findAll')->willReturn([]);

        $result = (new ListServicesHandler($repo))->handle(new ListServicesQuery());

        self::assertSame([], $result);
    }
}

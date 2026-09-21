<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\GetCatalogService\GetCatalogServiceHandler;
use App\Application\Service\GetCatalogService\GetCatalogServiceQuery;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class GetCatalogServiceHandlerTest extends TestCase
{
    public function testReturnsActiveService(): void
    {
        $service = TestFactory::service(id: 'candy-bar', name: 'Candy Bar');
        TestFactory::attachExamples($service, TestFactory::example($service));

        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);

        $result = (new GetCatalogServiceHandler($repo))->handle(new GetCatalogServiceQuery('candy-bar'));

        self::assertSame('candy-bar', $result['id']);
        self::assertCount(1, $result['examples']);
    }

    public function testReturnsNullForInactiveService(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')
            ->willReturn(TestFactory::service(id: 'oculto', isActive: false));

        $result = (new GetCatalogServiceHandler($repo))->handle(new GetCatalogServiceQuery('oculto'));

        self::assertNull($result);
    }

    public function testReturnsNullWhenServiceMissing(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $result = (new GetCatalogServiceHandler($repo))->handle(new GetCatalogServiceQuery('no-existe'));

        self::assertNull($result);
    }
}

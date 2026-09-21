<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\ListCatalogServices\ListCatalogServicesHandler;
use App\Application\Service\ListCatalogServices\ListCatalogServicesQuery;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class ListCatalogServicesHandlerTest extends TestCase
{
    public function testMapsOnlyActiveServicesWithFullRepresentation(): void
    {
        $service = TestFactory::service(id: 'candy-bar', name: 'Candy Bar', sortOrder: 1);
        TestFactory::attachExamples($service, TestFactory::example($service, title: 'Foto'));

        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->expects($this->once())->method('findAllActive')->willReturn([$service]);

        $result = (new ListCatalogServicesHandler($repo))->handle(new ListCatalogServicesQuery());

        self::assertCount(1, $result);
        self::assertSame('candy-bar', $result[0]['id']);
        self::assertSame('Candy Bar', $result[0]['name']);
        // Representación completa del catálogo (toArray de la entidad)
        self::assertArrayHasKey('features', $result[0]);
        self::assertCount(1, $result[0]['examples']);
        self::assertSame('Foto', $result[0]['examples'][0]['title']);
    }

    public function testReturnsEmptyArrayWhenNoActiveServices(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findAllActive')->willReturn([]);

        $result = (new ListCatalogServicesHandler($repo))->handle(new ListCatalogServicesQuery());

        self::assertSame([], $result);
    }
}

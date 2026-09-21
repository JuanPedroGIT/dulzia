<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\GetService\GetServiceHandler;
use App\Application\Service\GetService\GetServiceQuery;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class GetServiceHandlerTest extends TestCase
{
    public function testReturnsNullWhenServiceMissing(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $result = (new GetServiceHandler($repo))->handle(new GetServiceQuery('no-existe'));

        self::assertNull($result);
    }

    public function testReturnsServiceWithPhotos(): void
    {
        $service = TestFactory::service(
            id: 'candy-bar',
            name: 'Candy Bar',
            emoji: '🍬',
            description: 'Desc',
            features: ['A'],
            category: 'food',
            sortOrder: 4,
        );
        TestFactory::attachExamples(
            $service,
            TestFactory::example($service, title: 'Foto 1', sortOrder: 1),
            TestFactory::example($service, title: 'Foto 2', sortOrder: 2),
        );

        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);

        $result = (new GetServiceHandler($repo))->handle(new GetServiceQuery('candy-bar'));

        self::assertSame('candy-bar', $result['id']);
        self::assertSame('Candy Bar', $result['name']);
        self::assertSame(['A'], $result['features']);
        self::assertSame(4, $result['sort_order']);
        self::assertCount(2, $result['photos']);
        self::assertSame('Foto 1', $result['photos'][0]['title']);
        self::assertSame(1, $result['photos'][0]['sort_order']);
        self::assertArrayHasKey('imageUrl', $result['photos'][0]);
    }
}

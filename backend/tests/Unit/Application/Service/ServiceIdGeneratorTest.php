<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\CreateService\ServiceIdGenerator;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Entity\Service;
use PHPUnit\Framework\TestCase;

final class ServiceIdGeneratorTest extends TestCase
{
    public function testSlugifiesName(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $generator = new ServiceIdGenerator($repo);

        self::assertSame('candy-bar', $generator->generate('Candy Bar'));
        self::assertSame('photocall-360', $generator->generate('Photocall 360°'));
        self::assertSame('paella-gigante', $generator->generate('  Paella Gigante!!  '));
    }

    public function testAppendsSuffixWhenIdAlreadyExists(): void
    {
        $existing = new Service('candy-bar', 'Otro candy', '🍭', 'd', [], 'food');
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($existing);

        $id = (new ServiceIdGenerator($repo))->generate('Candy Bar');

        self::assertMatchesRegularExpression('/^candy-bar-\d{4}$/', $id);
    }

    public function testKeepsNameWhenIdFree(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $id = (new ServiceIdGenerator($repo))->generate('Candy Bar');

        self::assertSame('candy-bar', $id);
    }
}

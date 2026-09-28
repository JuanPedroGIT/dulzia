<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Banner;

use App\Application\Banner\UpdateBanner\UpdateBannerCommand;
use App\Application\Banner\UpdateBanner\UpdateBannerHandler;
use App\Domain\Banner\BannerRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Entity\Banner;
use PHPUnit\Framework\TestCase;

final class UpdateBannerHandlerTest extends TestCase
{
    public function testUpdatesTitleDescriptionAndWindow(): void
    {
        $banner = new Banner(
            'Antiguo',
            'Texto antiguo',
            new \DateTimeImmutable('2026-09-01'),
            new \DateTimeImmutable('2026-09-30'),
        );

        $banners = $this->createMock(BannerRepositoryInterface::class);
        $banners->method('find')->willReturn($banner);
        $banners->expects($this->once())->method('save')->with($banner);

        (new UpdateBannerHandler($banners))->handle(
            new UpdateBannerCommand(
                $banner->getId(),
                'Nuevo título',
                'Texto nuevo',
                '2026-10-01',
                '2026-11-30',
            ),
        );

        self::assertSame('Nuevo título', $banner->getTitle());
        self::assertSame('Texto nuevo', $banner->getDescription());
        self::assertSame('2026-10-01', $banner->getStartsAt()->format('Y-m-d'));
        self::assertSame('2026-11-30', $banner->getEndsAt()->format('Y-m-d'));
    }

    public function testThrowsWhenBannerNotFound(): void
    {
        $banners = $this->createMock(BannerRepositoryInterface::class);
        $banners->method('find')->willReturn(null);
        $banners->expects($this->never())->method('save');

        $this->expectException(NotFoundException::class);

        (new UpdateBannerHandler($banners))->handle(
            new UpdateBannerCommand('no-existe', 'Título', 'Descripción', '2026-10-01', '2026-10-31'),
        );
    }
}

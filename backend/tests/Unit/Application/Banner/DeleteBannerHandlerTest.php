<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Banner;

use App\Application\Banner\DeleteBanner\DeleteBannerCommand;
use App\Application\Banner\DeleteBanner\DeleteBannerHandler;
use App\Domain\Banner\BannerRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Entity\Banner;
use PHPUnit\Framework\TestCase;

final class DeleteBannerHandlerTest extends TestCase
{
    public function testDeletesTheBanner(): void
    {
        $banner = new Banner(
            'Aviso',
            'Descripción',
            new \DateTimeImmutable('2026-10-01'),
            new \DateTimeImmutable('2026-10-31'),
        );

        $banners = $this->createMock(BannerRepositoryInterface::class);
        $banners->method('find')->willReturn($banner);
        $banners->expects($this->once())->method('delete')->with($banner);

        (new DeleteBannerHandler($banners))->handle(new DeleteBannerCommand($banner->getId()));
    }

    public function testThrowsWhenBannerNotFound(): void
    {
        $banners = $this->createMock(BannerRepositoryInterface::class);
        $banners->method('find')->willReturn(null);
        $banners->expects($this->never())->method('delete');

        $this->expectException(NotFoundException::class);

        (new DeleteBannerHandler($banners))->handle(new DeleteBannerCommand('no-existe'));
    }
}

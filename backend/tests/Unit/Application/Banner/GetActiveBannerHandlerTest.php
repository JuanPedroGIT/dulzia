<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Banner;

use App\Application\Banner\GetActiveBanner\GetActiveBannerHandler;
use App\Application\Banner\GetActiveBanner\GetActiveBannerQuery;
use App\Domain\Banner\BannerRepositoryInterface;
use App\Entity\Banner;
use PHPUnit\Framework\TestCase;

final class GetActiveBannerHandlerTest extends TestCase
{
    private function handler(BannerRepositoryInterface $banners): GetActiveBannerHandler
    {
        return new GetActiveBannerHandler($banners);
    }

    private function banner(string $title = 'Aviso'): Banner
    {
        return new Banner($title, 'Descripción', new \DateTimeImmutable('2026-10-01'), new \DateTimeImmutable('2026-10-31'));
    }

    public function testReturnsNullWhenNothingIsInWindow(): void
    {
        $banners = $this->createMock(BannerRepositoryInterface::class);
        $banners->method('findActiveOn')->willReturn(null);

        $result = ($this->handler($banners))->handle(new GetActiveBannerQuery());

        self::assertSame(['banner' => null], $result);
    }

    public function testReturnsTheActiveBanner(): void
    {
        $banners = $this->createMock(BannerRepositoryInterface::class);
        $banners->method('findActiveOn')->willReturn($this->banner());

        $result = ($this->handler($banners))->handle(new GetActiveBannerQuery());

        self::assertSame('Aviso', $result['banner']['title']);
        self::assertSame('2026-10-01', $result['banner']['starts_at']);
        self::assertSame('2026-10-31', $result['banner']['ends_at']);
    }

    public function testUsesTodayWhenNoDateIsGiven(): void
    {
        $banners = $this->createMock(BannerRepositoryInterface::class);
        $banners->expects($this->once())
            ->method('findActiveOn')
            ->with($this->callback(
                static fn (\DateTimeImmutable $date): bool =>
                    $date->format('Y-m-d') === (new \DateTimeImmutable('today'))->format('Y-m-d')
            ))
            ->willReturn(null);

        ($this->handler($banners))->handle(new GetActiveBannerQuery());
    }

    public function testUsesTheGivenDate(): void
    {
        $when = new \DateTimeImmutable('2026-12-25');

        $banners = $this->createMock(BannerRepositoryInterface::class);
        $banners->expects($this->once())
            ->method('findActiveOn')
            ->with($when)
            ->willReturn($this->banner('Navidad'));

        $result = ($this->handler($banners))->handle(new GetActiveBannerQuery($when));

        self::assertSame('Navidad', $result['banner']['title']);
    }
}

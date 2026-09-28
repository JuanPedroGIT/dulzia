<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Banner;

use App\Application\Banner\CreateBanner\CreateBannerCommand;
use App\Application\Banner\CreateBanner\CreateBannerHandler;
use App\Domain\Banner\BannerRepositoryInterface;
use App\Entity\Banner;
use PHPUnit\Framework\TestCase;

final class CreateBannerHandlerTest extends TestCase
{
    public function testSavesTheBannerWithDatesAtMidnight(): void
    {
        $saved = null;

        $banners = $this->createMock(BannerRepositoryInterface::class);
        $banners->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Banner $banner) use (&$saved): bool {
                $saved = $banner;

                return true;
            }));

        $result = (new CreateBannerHandler($banners))->handle(
            new CreateBannerCommand('Nuevo servicio', 'Ya estamos en toda la península.', '2026-10-01', '2026-10-31'),
        );

        self::assertInstanceOf(Banner::class, $saved);
        self::assertSame('Nuevo servicio', $saved->getTitle());
        self::assertSame('Ya estamos en toda la península.', $saved->getDescription());
        // Días de calendario a medianoche, como pide la ventana.
        self::assertSame('2026-10-01 00:00:00', $saved->getStartsAt()->format('Y-m-d H:i:s'));
        self::assertSame('2026-10-31 00:00:00', $saved->getEndsAt()->format('Y-m-d H:i:s'));

        // Lo que vuelve al panel es el banner tal cual se guardó.
        self::assertSame($saved->toArray(), $result);
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Banner;

use App\Application\Banner\BannerCommandFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class BannerCommandFactoryTest extends TestCase
{
    private function request(string $body): Request
    {
        return Request::create('/api/admin/banners', 'POST', [], [], [], ['CONTENT_TYPE' => 'application/json'], $body);
    }

    public function testCreatesTheCommandFromTheBody(): void
    {
        $command = (new BannerCommandFactory())->createFromRequest($this->request(json_encode([
            'title' => 'Nuevo servicio',
            'description' => 'Ya estamos en toda la península.',
            'starts_at' => '2026-10-01',
            'ends_at' => '2026-10-31',
        ], JSON_THROW_ON_ERROR)));

        self::assertSame('Nuevo servicio', $command->title);
        self::assertSame('Ya estamos en toda la península.', $command->description);
        self::assertSame('2026-10-01', $command->startsAt);
        self::assertSame('2026-10-31', $command->endsAt);
    }

    public function testUpdateTakesTheIdFromTheUrl(): void
    {
        $command = (new BannerCommandFactory())->updateFromRequest('abc123', $this->request(json_encode([
            'title' => 'Título',
            'description' => 'Descripción',
            'starts_at' => '2026-10-01',
            'ends_at' => '2026-10-31',
        ], JSON_THROW_ON_ERROR)));

        self::assertSame('abc123', $command->id);
    }

    public function testMissingFieldsArriveEmptyForTheValidator(): void
    {
        // El command no valida: los huecos llegan vacíos y los pilla el NotBlank.
        $command = (new BannerCommandFactory())->createFromRequest($this->request('{}'));

        self::assertSame('', $command->title);
        self::assertSame('', $command->description);
        self::assertSame('', $command->startsAt);
        self::assertSame('', $command->endsAt);
    }

    public function testNonJsonBodyArrivesEmpty(): void
    {
        $command = (new BannerCommandFactory())->createFromRequest($this->request('no-es-json'));

        self::assertSame('', $command->title);
        self::assertSame('', $command->startsAt);
    }
}

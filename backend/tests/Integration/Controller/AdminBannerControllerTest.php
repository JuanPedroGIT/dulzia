<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\Banner;
use App\Tests\Integration\IntegrationTestCase;

final class AdminBannerControllerTest extends IntegrationTestCase
{
    private const ROUTE = '/api/admin/banners';

    // ── Auth ──────────────────────────────────────────────────────────────

    public function testRejectsRequestsWithoutToken(): void
    {
        $this->client()->request('GET', self::ROUTE);

        self::assertResponseStatusCodeSame(401);
    }

    // ── Lectura ───────────────────────────────────────────────────────────

    public function testStartsEmpty(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('GET', self::ROUTE, [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        self::assertSame([], json_decode((string) $client->getResponse()->getContent(), true));
    }

    public function testListsBannersNewestFirst(): void
    {
        $this->createAdminUser();
        $older = new Banner('Antiguo', 'Uno', new \DateTimeImmutable('2026-10-01'), new \DateTimeImmutable('2026-10-31'));
        $newer = new Banner('Nuevo', 'Dos', new \DateTimeImmutable('2026-11-01'), new \DateTimeImmutable('2026-11-30'));
        $em = $this->em();
        $em->persist($older);
        $em->persist($newer);
        $em->flush();
        // Tocar el antiguo lo convierte en el más reciente (así se ordena sin
        // depender de que dos timestamps del mismo microsegundo difieran).
        // Sin clear: sigue managed y el cambio sale como UPDATE.
        //
        // La pausa evita el empate: la columna es TIMESTAMP(0) y tres escrituras
        // en el mismo segundo ordenarían igual por updated_at (en producción un
        // humano no las hace tan seguidas, pero el test sí).
        usleep(1_100_000);
        $older->update('Antiguo (editado)', 'Uno', new \DateTimeImmutable('2026-10-01'), new \DateTimeImmutable('2026-10-31'));
        $em->flush();

        $client = $this->client();
        $client->request('GET', self::ROUTE, [], [], $this->authHeaders());

        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame(['Antiguo (editado)', 'Nuevo'], array_column($data, 'title'));
    }

    // ── Alta ──────────────────────────────────────────────────────────────

    public function testCreatesABanner(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('POST', self::ROUTE, [], [], $this->jsonHeaders($this->authHeaders()), $this->body([
            'title' => 'Nuevo servicio',
            'description' => 'Ya estamos en toda la península.',
            'starts_at' => '2026-10-01',
            'ends_at' => '2026-10-31',
        ]));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('Nuevo servicio', $data['title']);
        self::assertSame('2026-10-01', $data['starts_at']);
        self::assertSame('2026-10-31', $data['ends_at']);
        self::assertArrayHasKey('id', $data);
    }

    public function testBlankFieldsReturn422(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('POST', self::ROUTE, [], [], $this->jsonHeaders($this->authHeaders()), $this->body([
            'title' => '',
            'description' => '',
            'starts_at' => '',
            'ends_at' => '',
        ]));

        self::assertResponseStatusCodeSame(422);
        self::assertSame([], $this->storedBanners(), 'Un alta inválida no escribe nada');
    }

    public function testMalformedDateReturns422(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('POST', self::ROUTE, [], [], $this->jsonHeaders($this->authHeaders()), $this->body([
            'title' => 'Título',
            'description' => 'Descripción',
            'starts_at' => '2026-13-40',
            'ends_at' => '2026-10-31',
        ]));

        self::assertResponseStatusCodeSame(422);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertStringContainsString('La fecha de inicio no es válida', implode(' ', $data['errors']['startsAt'] ?? []));
    }

    public function testWindowBackwardsReturns422(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('POST', self::ROUTE, [], [], $this->jsonHeaders($this->authHeaders()), $this->body([
            'title' => 'Título',
            'description' => 'Descripción',
            'starts_at' => '2026-10-31',
            'ends_at' => '2026-10-01',
        ]));

        self::assertResponseStatusCodeSame(422);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertStringContainsString('La fecha de fin no puede ser anterior a la de inicio', json_encode($data['errors'], JSON_THROW_ON_ERROR));
    }

    // ── Edición ───────────────────────────────────────────────────────────

    public function testUpdatesABanner(): void
    {
        $this->createAdminUser();
        $banner = new Banner('Antiguo', 'Texto', new \DateTimeImmutable('2026-10-01'), new \DateTimeImmutable('2026-10-31'));
        $this->persist($banner);

        $client = $this->client();
        $client->request('PUT', self::ROUTE . '/' . $banner->getId(), [], [], $this->jsonHeaders($this->authHeaders()), $this->body([
            'title' => 'Nuevo título',
            'description' => 'Texto nuevo',
            'starts_at' => '2026-11-01',
            'ends_at' => '2026-11-30',
        ]));
        self::assertResponseIsSuccessful();

        $client->request('GET', self::ROUTE, [], [], $this->authHeaders());
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('Nuevo título', $data[0]['title']);
        self::assertSame('2026-11-01', $data[0]['starts_at']);
    }

    public function testUpdateUnknownIdReturns404(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('PUT', self::ROUTE . '/no-existe', [], [], $this->jsonHeaders($this->authHeaders()), $this->body([
            'title' => 'Título',
            'description' => 'Descripción',
            'starts_at' => '2026-10-01',
            'ends_at' => '2026-10-31',
        ]));

        self::assertResponseStatusCodeSame(404);
    }

    // ── Borrado ───────────────────────────────────────────────────────────

    public function testDeletesABanner(): void
    {
        $this->createAdminUser();
        $banner = new Banner('Aviso', 'Texto', new \DateTimeImmutable('2026-10-01'), new \DateTimeImmutable('2026-10-31'));
        $this->persist($banner);

        $client = $this->client();
        $client->request('DELETE', self::ROUTE . '/' . $banner->getId(), [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        self::assertSame([], $this->storedBanners());
    }

    public function testDeleteUnknownIdReturns404(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('DELETE', self::ROUTE . '/no-existe', [], [], $this->authHeaders());

        self::assertResponseStatusCodeSame(404);
    }

    // ── Apoyo ─────────────────────────────────────────────────────────────

    /** @param array<string, mixed> $data */
    private function body(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR);
    }

    /** @param array<string, string> $headers */
    private function jsonHeaders(array $headers = []): array
    {
        return ['CONTENT_TYPE' => 'application/json'] + $headers;
    }

    /** @return array<int, array<string, mixed>> */
    private function storedBanners(): array
    {
        return array_map(
            static fn (Banner $banner) => $banner->toArray(),
            $this->em()->getRepository(Banner::class)->findAll(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\Banner;
use App\Tests\Integration\IntegrationTestCase;

final class BannerControllerTest extends IntegrationTestCase
{
    private const ROUTE = '/api/banner';

    public function testReturnsNullWhenNothingIsConfigured(): void
    {
        $client = $this->client();
        $client->request('GET', self::ROUTE);

        self::assertResponseIsSuccessful();
        self::assertSame(
            ['banner' => null],
            json_decode((string) $client->getResponse()->getContent(), true),
        );
    }

    public function testReturnsTheBannerInWindowToday(): void
    {
        $this->persist(new Banner(
            'Nuevo servicio',
            'Ya estamos en toda la península.',
            new \DateTimeImmutable('yesterday'),
            new \DateTimeImmutable('tomorrow'),
        ));

        $client = $this->client();
        $client->request('GET', self::ROUTE);

        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('Nuevo servicio', $data['banner']['title']);
        self::assertSame('Ya estamos en toda la península.', $data['banner']['description']);
        // Las fechas se publican: la web no las enseña, pero el dato viaja completo.
        self::assertSame((new \DateTimeImmutable('yesterday'))->format('Y-m-d'), $data['banner']['starts_at']);
    }

    public function testBordersAreInclusive(): void
    {
        // Una ventana que empieza o acaba hoy sigue estando en ventana.
        $this->persist(new Banner(
            'Solo hoy',
            'Último día.',
            new \DateTimeImmutable('today'),
            new \DateTimeImmutable('today'),
        ));

        $client = $this->client();
        $client->request('GET', self::ROUTE);

        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('Solo hoy', $data['banner']['title']);
    }

    public function testIgnoresExpiredAndFutureBanners(): void
    {
        $this->persist(
            new Banner('Caducado', 'Ayer', new \DateTimeImmutable('-10 days'), new \DateTimeImmutable('-5 days')),
            new Banner('Futuro', 'Mañana', new \DateTimeImmutable('+5 days'), new \DateTimeImmutable('+10 days')),
        );

        $client = $this->client();
        $client->request('GET', self::ROUTE);

        self::assertSame(
            ['banner' => null],
            json_decode((string) $client->getResponse()->getContent(), true),
        );
    }

    public function testMostRecentlyUpdatedWinsWhenSeveralAreActive(): void
    {
        $first = new Banner('Primero', 'Uno', new \DateTimeImmutable('today'), new \DateTimeImmutable('tomorrow'));
        $second = new Banner('Segundo', 'Dos', new \DateTimeImmutable('today'), new \DateTimeImmutable('tomorrow'));
        $em = $this->em();
        $em->persist($first);
        $em->persist($second);
        $em->flush();
        // Editar el primero lo convierte en el más reciente y, por tanto, en el
        // que manda (la regla del "activo" con varios en ventana).
        // Sin clear: sigue managed y el cambio sale como UPDATE.
        //
        // La pausa evita el empate: la columna es TIMESTAMP(0) y tres escrituras
        // en el mismo segundo ordenarían igual por updated_at (en producción un
        // humano no las hace tan seguidas, pero el test sí).
        usleep(1_100_000);
        $first->update('Primero (editado)', 'Uno', new \DateTimeImmutable('today'), new \DateTimeImmutable('tomorrow'));
        $em->flush();

        $client = $this->client();
        $client->request('GET', self::ROUTE);

        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('Primero (editado)', $data['banner']['title']);
    }
}

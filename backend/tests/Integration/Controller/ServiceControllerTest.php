<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Support\TestFactory;

final class ServiceControllerTest extends IntegrationTestCase
{
    public function testListsOnlyActiveServicesSorted(): void
    {
        $this->persist(
            TestFactory::service(id: 'inactivo', name: 'Inactivo', sortOrder: 0, isActive: false),
            TestFactory::service(id: 'a', name: 'Primero', sortOrder: 1),
            TestFactory::service(id: 'b', name: 'Segundo', sortOrder: 2),
        );

        $client = $this->client();
        $client->request('GET', '/api/services');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertCount(2, $data);
        self::assertSame('a', $data[0]['id']);
        self::assertSame('b', $data[1]['id']);
    }

    public function testShowReturnsServiceWithExamples(): void
    {
        $service = TestFactory::service(id: 'candy-bar', name: 'Candy Bar');
        $this->persist($service, TestFactory::example($service, title: 'Foto de la cabina'));

        $client = $this->client();
        $client->request('GET', '/api/services/candy-bar');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame('candy-bar', $data['id']);
        self::assertSame('Candy Bar', $data['name']);
        self::assertCount(1, $data['examples']);
        self::assertSame('Foto de la cabina', $data['examples'][0]['title']);
    }

    public function testShowExposesThumbnailForEachExample(): void
    {
        $service = TestFactory::service(id: 'candy-bar', name: 'Candy Bar');
        $this->persist(
            $service,
            TestFactory::example(
                $service,
                title: 'Con miniatura',
                imageUrl: 'https://fake-storage.test/services/grande.jpg',
                thumbnailUrl: 'https://fake-storage.test/services/mini.jpg',
                sortOrder: 1,
            ),
            TestFactory::example(
                $service,
                title: 'Sin miniatura',
                imageUrl: 'https://fake-storage.test/services/sola.jpg',
                sortOrder: 2,
            ),
        );

        $client = $this->client();
        $client->request('GET', '/api/services/candy-bar');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame('https://fake-storage.test/services/mini.jpg', $data['examples'][0]['thumbnail']);
        self::assertSame('https://fake-storage.test/services/grande.jpg', $data['examples'][0]['image']);
        // Las fotos antiguas no tienen miniatura: la rejilla sirve la grande.
        self::assertSame('https://fake-storage.test/services/sola.jpg', $data['examples'][1]['thumbnail']);
    }

    public function testShowReturns404ForUnknownService(): void
    {
        $client = $this->client();
        $client->request('GET', '/api/services/no-existe');

        self::assertResponseStatusCodeSame(404);
    }

    public function testShowReturns404ForInactiveService(): void
    {
        $this->persist(TestFactory::service(id: 'oculto', isActive: false));

        $client = $this->client();
        $client->request('GET', '/api/services/oculto');

        self::assertResponseStatusCodeSame(404);
    }
}

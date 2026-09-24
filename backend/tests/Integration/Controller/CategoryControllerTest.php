<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Support\TestFactory;

final class CategoryControllerTest extends IntegrationTestCase
{
    public function testPublicListIsOpenAndReturnsTheSeedCategories(): void
    {
        $client = $this->client();
        $client->request('GET', '/api/categories');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame(['food', 'decoration', 'experience'], array_column($data, 'id'));
        self::assertSame('Gastronomía', $data[0]['name']);
        self::assertSame('🍴', $data[0]['emoji']);
        self::assertSame(0, $data[0]['sort_order']);
    }

    public function testAdminListIncludesHowManyServicesUseEach(): void
    {
        $this->createAdminUser();
        $this->persist(
            TestFactory::service(id: 'a', category: 'food'),
            TestFactory::service(id: 'b', category: 'food'),
            TestFactory::service(id: 'c', category: 'decoration'),
        );

        $client = $this->client();
        $client->request('GET', '/api/admin/categories', [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        $byId = array_column($data, null, 'id');

        self::assertSame(2, $byId['food']['serviceCount']);
        self::assertSame(1, $byId['decoration']['serviceCount']);
        self::assertSame(0, $byId['experience']['serviceCount']);
    }

    public function testAdminListRequiresToken(): void
    {
        $client = $this->client();
        $client->request('GET', '/api/admin/categories');

        self::assertResponseStatusCodeSame(401);
    }

    public function testCreatesCategoryWithGeneratedSlug(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request(
            'POST',
            '/api/admin/categories',
            [],
            [],
            $this->authHeaders() + ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Animación', 'emoji' => '🎪', 'sort_order' => 3], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame('animacion', $data['id']);
        self::assertSame('Animación', $data['name']);
    }

    public function testCreateWithoutNameReturns400(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request(
            'POST',
            '/api/admin/categories',
            [],
            [],
            $this->authHeaders() + ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => '  '], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
    }

    public function testUpdatesNameEmojiAndOrder(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request(
            'PUT',
            '/api/admin/categories/food',
            [],
            [],
            $this->authHeaders() + ['CONTENT_TYPE' => 'application/json'],
            json_encode(['name' => 'Comida', 'emoji' => '🍕', 'sort_order' => 9], JSON_THROW_ON_ERROR),
        );

        self::assertResponseIsSuccessful();

        $public = $this->client();
        $public->request('GET', '/api/categories');
        $food = array_column(
            json_decode((string) $public->getResponse()->getContent(), true),
            null,
            'id',
        )['food'];

        self::assertSame('Comida', $food['name']);
        self::assertSame('🍕', $food['emoji']);
        self::assertSame(9, $food['sort_order']);
    }

    public function testDeletesUnusedCategory(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('DELETE', '/api/admin/categories/experience', [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();

        $public = $this->client();
        $public->request('GET', '/api/categories');
        $ids = array_column(json_decode((string) $public->getResponse()->getContent(), true), 'id');

        self::assertSame(['food', 'decoration'], $ids);
    }

    public function testBlocksDeletingCategoryInUse(): void
    {
        $this->createAdminUser();
        $this->persist(
            TestFactory::service(id: 'a', category: 'food'),
            TestFactory::service(id: 'b', category: 'food'),
        );

        $client = $this->client();
        $client->request('DELETE', '/api/admin/categories/food', [], [], $this->authHeaders());

        self::assertResponseStatusCodeSame(409);

        $error = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('No se puede borrar: la usan 2 secciones.', $error['error']);
    }

    public function testDeletingUnknownCategoryReturns404(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('DELETE', '/api/admin/categories/no-existe', [], [], $this->authHeaders());

        self::assertResponseStatusCodeSame(404);
    }

    public function testCreatingServiceWithUnknownCategoryReturns400(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request(
            'POST',
            '/api/admin/services',
            [],
            [],
            $this->authHeaders() + ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Sección nueva',
                'description' => 'D',
                'category' => 'inventada',
            ], JSON_THROW_ON_ERROR),
        );

        self::assertResponseStatusCodeSame(400);
        $error = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('La categoría "inventada" no existe', $error['error']);
    }
}

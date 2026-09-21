<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\Service;
use App\Entity\ServiceExample;
use App\Domain\Storage\FileStorageInterface;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Support\TestFactory;

final class AdminServiceControllerTest extends IntegrationTestCase
{
    private function fakeStorage(): \App\Tests\TestDoubles\FakeFileStorage
    {
        $storage = static::getContainer()->get(FileStorageInterface::class);
        self::assertInstanceOf(\App\Tests\TestDoubles\FakeFileStorage::class, $storage);

        return $storage;
    }

    // ── Auth ──────────────────────────────────────────────────────────────

    public function testRejectsRequestsWithoutToken(): void
    {
        $client = $this->client();
        $client->request('GET', '/api/admin/services');

        self::assertResponseStatusCodeSame(401);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('No autorizado', $data['error']);
    }

    // ── Servicios ─────────────────────────────────────────────────────────

    public function testListsAllServicesIncludingInactive(): void
    {
        $this->createAdminUser();
        $this->persist(
            TestFactory::service(id: 'a', sortOrder: 1),
            TestFactory::service(id: 'b', sortOrder: 2, isActive: false),
        );

        $client = $this->client();
        $client->request('GET', '/api/admin/services', [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertCount(2, $data);
        self::assertTrue($data[0]['is_active']);
        self::assertFalse($data[1]['is_active']);
    }

    public function testCreatesService(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('POST', '/api/admin/services', [], [], $this->authHeaders() + [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Candy Bar',
            'emoji' => '🍬',
            'description' => 'Candy bar para eventos',
            'features' => ['Chuches', 'Personalizado'],
            'category' => 'food',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('candy-bar', $data['id']);

        $saved = $this->em()->getRepository(Service::class)->find('candy-bar');
        self::assertNotNull($saved);
        self::assertSame('Candy Bar', $saved->getName());
        self::assertSame(['Chuches', 'Personalizado'], $saved->getFeatures());
    }

    public function testCreateRejectsMissingFields(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('POST', '/api/admin/services', [], [], $this->authHeaders() + [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['name' => '', 'emoji' => '', 'description' => ''], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(400);
    }

    public function testUpdatesService(): void
    {
        $this->createAdminUser();
        $this->persist(TestFactory::service(id: 'candy-bar', name: 'Antiguo'));

        $client = $this->client();
        $client->request('PUT', '/api/admin/services/candy-bar', [], [], $this->authHeaders() + [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'Candy Bar XL',
            'emoji' => '🍭',
            'description' => 'Nueva descripción',
            'features' => ['A'],
            'category' => 'food',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseIsSuccessful();

        $this->em()->clear();
        $saved = $this->em()->getRepository(Service::class)->find('candy-bar');
        self::assertSame('Candy Bar XL', $saved->getName());
        self::assertSame('🍭', $saved->getEmoji());
    }

    public function testUpdateUnknownServiceReturns404(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('PUT', '/api/admin/services/no-existe', [], [], $this->authHeaders() + [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'X', 'emoji' => 'E', 'description' => 'D', 'features' => [], 'category' => 'food',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(404);
    }

    public function testDeactivatesAndActivatesService(): void
    {
        $this->createAdminUser();
        $this->persist(TestFactory::service(id: 'candy-bar'));

        $client = $this->client();
        $headers = $this->authHeaders();

        $client->request('DELETE', '/api/admin/services/candy-bar', [], [], $headers);
        self::assertResponseIsSuccessful();
        $this->em()->clear();
        self::assertFalse($this->em()->getRepository(Service::class)->find('candy-bar')->isActive());

        $client->request('POST', '/api/admin/services/candy-bar/activate', [], [], $headers);
        self::assertResponseIsSuccessful();
        $this->em()->clear();
        self::assertTrue($this->em()->getRepository(Service::class)->find('candy-bar')->isActive());
    }

    // ── Fotos ─────────────────────────────────────────────────────────────

    public function testAddPhotoWithFileUsesStorage(): void
    {
        $this->createAdminUser();
        $this->persist(TestFactory::service(id: 'candy-bar'));

        $client = $this->client();
        $client->request(
            'POST',
            '/api/admin/services/candy-bar/photos',
            ['title' => 'Foto cabina', 'description' => 'Nuestra cabina'],
            ['image' => TestFactory::uploadedFile()],
            $this->authHeaders(),
        );

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertMatchesRegularExpression(
            '#^https://fake-storage\.test/services/[0-9a-f]{32}\.png$#',
            $data['imageUrl'],
        );

        $examples = $this->em()->getRepository(ServiceExample::class)->findAll();
        self::assertCount(1, $examples);
        self::assertSame($data['imageUrl'], $examples[0]->getImageUrl());
    }

    public function testAddPhotoWithExternalImageUrl(): void
    {
        $this->createAdminUser();
        $this->persist(TestFactory::service(id: 'candy-bar'));

        $client = $this->client();
        $client->request(
            'POST',
            '/api/admin/services/candy-bar/photos',
            ['title' => 'Foto', 'description' => 'Desc', 'imageUrl' => 'https://picsum.photos/200'],
            [],
            $this->authHeaders(),
        );

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('https://picsum.photos/200', $data['imageUrl']);
    }

    public function testAddPhotoForUnknownServiceReturns404(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request(
            'POST',
            '/api/admin/services/no-existe/photos',
            ['title' => 'Foto', 'description' => 'Desc'],
            [],
            $this->authHeaders(),
        );

        self::assertResponseStatusCodeSame(404);
    }

    public function testUpdatePhotoWithFileDeletesOldFromStorage(): void
    {
        $this->createAdminUser();
        $service = TestFactory::service(id: 'candy-bar');
        $example = TestFactory::example($service, imageUrl: 'https://fake-storage.test/services/vieja.jpg');
        $this->persist($service, $example);

        $client = $this->client();
        $client->request(
            'POST',
            '/api/admin/photos/' . $example->getId(),
            ['title' => 'Nueva foto'],
            ['image' => TestFactory::uploadedFile()],
            $this->authHeaders(),
        );

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertMatchesRegularExpression(
            '#^https://fake-storage\.test/services/[0-9a-f]{32}\.png$#',
            $data['imageUrl'],
        );
        self::assertContains(
            'https://fake-storage.test/services/vieja.jpg',
            $this->fakeStorage()->deletedUrls,
            'La foto antigua debe borrarse del storage',
        );
    }

    public function testDeletePhotoRemovesRecordAndStorageFile(): void
    {
        $this->createAdminUser();
        $service = TestFactory::service(id: 'candy-bar');
        $example = TestFactory::example($service, imageUrl: 'https://fake-storage.test/services/borrar.jpg');
        $this->persist($service, $example);

        $client = $this->client();
        $client->request('DELETE', '/api/admin/photos/' . $example->getId(), [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        self::assertCount(0, $this->em()->getRepository(ServiceExample::class)->findAll());
        self::assertContains(
            'https://fake-storage.test/services/borrar.jpg',
            $this->fakeStorage()->deletedUrls,
        );
    }

    public function testPhotoNotFoundReturns404(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('DELETE', '/api/admin/photos/no-existe', [], [], $this->authHeaders());

        self::assertResponseStatusCodeSame(404);
    }
}

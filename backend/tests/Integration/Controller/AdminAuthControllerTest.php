<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Tests\Integration\IntegrationTestCase;

final class AdminAuthControllerTest extends IntegrationTestCase
{
    public function testLoginReturnsToken(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('POST', '/api/admin/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['username' => 'admin', 'password' => 'admin'], JSON_THROW_ON_ERROR));

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $data['token']);

        // El token queda persistido (un solo token válido a la vez)
        $count = $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM admin_token');
        self::assertSame(1, (int) $count);
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('POST', '/api/admin/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode(['username' => 'admin', 'password' => 'incorrecta'], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(401);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('Credenciales incorrectas', $data['error']);

        $count = $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM admin_token');
        self::assertSame(0, (int) $count);
    }

    public function testLoginRequiresCredentials(): void
    {
        $client = $this->client();
        $client->request('POST', '/api/admin/login', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(400);
    }

    public function testLogoutInvalidatesToken(): void
    {
        $this->createAdminUser();
        $token = $this->login();

        $client = $this->client();
        $client->request('POST', '/api/admin/logout', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        self::assertResponseIsSuccessful();

        $count = $this->em()->getConnection()->fetchOne('SELECT COUNT(*) FROM admin_token');
        self::assertSame(0, (int) $count);
    }

    public function testLogoutRequiresValidToken(): void
    {
        // Protegido por AdminAuthListener como el resto de /api/admin
        $client = $this->client();
        $client->request('POST', '/api/admin/logout');

        self::assertResponseStatusCodeSame(401);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('No autorizado', $data['error']);
    }

    public function testCheckAcceptsValidToken(): void
    {
        $this->createAdminUser();
        $token = $this->login();

        $client = $this->client();
        $client->request('GET', '/api/admin/check', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . $token,
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame(['ok' => true], $data);
    }

    public function testCheckRejectsMissingToken(): void
    {
        $client = $this->client();
        $client->request('GET', '/api/admin/check');

        self::assertResponseStatusCodeSame(401);
    }

    public function testCheckRejectsUnknownToken(): void
    {
        // Token con el formato correcto pero inexistente en admin_token
        $client = $this->client();
        $client->request('GET', '/api/admin/check', [], [], [
            'HTTP_AUTHORIZATION' => 'Bearer ' . str_repeat('a', 64),
        ]);

        self::assertResponseStatusCodeSame(401);
    }
}

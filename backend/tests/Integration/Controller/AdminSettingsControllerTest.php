<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Domain\Settings\SettingKey;
use App\Entity\Setting;
use App\Tests\Integration\IntegrationTestCase;

final class AdminSettingsControllerTest extends IntegrationTestCase
{
    private const ROUTE = '/api/admin/settings/contact-recipient';

    // Datos que se publican en la web: otro recurso, con sus propias reglas
    // (aquí el teléfono y el email de contacto, sin nombre de destinatario).
    private const DETAILS_ROUTE = '/api/admin/settings/contact-details';

    // ── Auth ──────────────────────────────────────────────────────────────

    public function testRejectsRequestsWithoutToken(): void
    {
        $this->client()->request('GET', self::ROUTE);

        self::assertResponseStatusCodeSame(401);
    }

    // ── Lectura ───────────────────────────────────────────────────────────

    public function testReturnsEnvDefaultsWhenNothingIsConfigured(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('GET', self::ROUTE, [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame($this->defaultEmail(), $data['email']);
        self::assertSame($this->defaultName(), $data['name']);
        self::assertSame('env', $data['email_source']);
        self::assertSame('env', $data['name_source']);
    }

    public function testReturnsConfiguredValuesAfterUpdate(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $headers = $this->authHeaders();

        $client->request('PUT', self::ROUTE, [], [], $this->jsonHeaders($headers), json_encode([
            'email' => 'panel@example.com',
            'name' => 'Nombre del panel',
        ], JSON_THROW_ON_ERROR));
        self::assertResponseIsSuccessful();

        $client->request('GET', self::ROUTE, [], [], $headers);
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame('panel@example.com', $data['email']);
        self::assertSame('Nombre del panel', $data['name']);
        self::assertSame('db', $data['email_source']);
        self::assertSame('db', $data['name_source']);
    }

    // ── Escritura ─────────────────────────────────────────────────────────

    public function testStoresTrimmedValues(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('PUT', self::ROUTE, [], [], $this->jsonHeaders($this->authHeaders()), json_encode([
            'email' => '  panel@example.com  ',
            'name' => '  Nombre del panel  ',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseIsSuccessful();
        $stored = $this->storedSettings();
        self::assertCount(2, $stored);
        self::assertSame('panel@example.com', $stored[SettingKey::CONTACT_RECIPIENT_EMAIL] ?? null);
        self::assertSame('Nombre del panel', $stored[SettingKey::CONTACT_RECIPIENT_NAME] ?? null);
    }

    public function testEmptyingTheFieldsGoesBackToEnv(): void
    {
        $this->createAdminUser();
        $this->persist(
            new Setting(SettingKey::CONTACT_RECIPIENT_EMAIL, 'panel@example.com'),
            new Setting(SettingKey::CONTACT_RECIPIENT_NAME, 'Nombre del panel'),
        );

        $client = $this->client();
        $headers = $this->authHeaders();

        $client->request('PUT', self::ROUTE, [], [], $this->jsonHeaders($headers), json_encode([
            'email' => '',
            'name' => '',
        ], JSON_THROW_ON_ERROR));
        self::assertResponseIsSuccessful();

        self::assertSame([], $this->storedSettings());

        $client->request('GET', self::ROUTE, [], [], $headers);
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame($this->defaultEmail(), $data['email']);
        self::assertSame('env', $data['email_source']);
    }

    public function testEachFieldFallsBackIndependently(): void
    {
        $this->createAdminUser();
        $this->persist(new Setting(SettingKey::CONTACT_RECIPIENT_EMAIL, 'panel@example.com'));

        $client = $this->client();
        $client->request('GET', self::ROUTE, [], [], $this->authHeaders());
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame('panel@example.com', $data['email']);
        self::assertSame('db', $data['email_source']);
        self::assertSame($this->defaultName(), $data['name']);
        self::assertSame('env', $data['name_source']);
    }

    public function testInvalidEmailReturns422AndWritesNothing(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('PUT', self::ROUTE, [], [], $this->jsonHeaders($this->authHeaders()), json_encode([
            'email' => 'no-es-email',
            'name' => 'Nombre del panel',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
        self::assertSame([], $this->storedSettings());
    }

    public function testMissingFieldsAreTreatedAsEmpty(): void
    {
        $this->createAdminUser();
        $this->persist(new Setting(SettingKey::CONTACT_RECIPIENT_EMAIL, 'panel@example.com'));

        $client = $this->client();
        $client->request('PUT', self::ROUTE, [], [], $this->jsonHeaders($this->authHeaders()), '{}');

        self::assertResponseIsSuccessful();
        self::assertSame([], $this->storedSettings(), 'Un PUT sin campos debe limpiar el ajuste');
    }

    // ── Datos de contacto publicados en la web ────────────────────────────

    public function testContactDetailsRejectRequestsWithoutToken(): void
    {
        $this->client()->request('GET', self::DETAILS_ROUTE);

        self::assertResponseStatusCodeSame(401);
    }

    public function testContactDetailsStartUnconfigured(): void
    {
        // Sin nada en el panel no se inventa un valor: la web enseña el suyo.
        $this->createAdminUser();

        $client = $this->client();
        $client->request('GET', self::DETAILS_ROUTE, [], [], $this->authHeaders());
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertResponseIsSuccessful();
        self::assertNull($data['email']);
        self::assertNull($data['phone']);
        self::assertSame('default', $data['email_source']);
        self::assertSame('default', $data['phone_source']);
    }

    public function testContactDetailsStoreTrimmedValues(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $headers = $this->authHeaders();

        $client->request('PUT', self::DETAILS_ROUTE, [], [], $this->jsonHeaders($headers), json_encode([
            'email' => '  hola@example.com  ',
            'phone' => '  +34 629 991 659  ',
        ], JSON_THROW_ON_ERROR));
        self::assertResponseIsSuccessful();

        $stored = $this->storedSettings();
        self::assertCount(2, $stored);
        self::assertSame('hola@example.com', $stored[SettingKey::CONTACT_EMAIL] ?? null);
        self::assertSame('+34 629 991 659', $stored[SettingKey::CONTACT_PHONE] ?? null);

        $client->request('GET', self::DETAILS_ROUTE, [], [], $headers);
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame('hola@example.com', $data['email']);
        self::assertSame('+34 629 991 659', $data['phone']);
        self::assertSame('db', $data['email_source']);
        self::assertSame('db', $data['phone_source']);
    }

    public function testContactDetailsGoBackToTheWebDefaultWhenEmptied(): void
    {
        $this->createAdminUser();
        $this->persist(
            new Setting(SettingKey::CONTACT_EMAIL, 'hola@example.com'),
            new Setting(SettingKey::CONTACT_PHONE, '+34 629 991 659'),
        );

        $client = $this->client();
        $headers = $this->authHeaders();

        $client->request('PUT', self::DETAILS_ROUTE, [], [], $this->jsonHeaders($headers), json_encode([
            'email' => '',
            'phone' => '',
        ], JSON_THROW_ON_ERROR));
        self::assertResponseIsSuccessful();

        self::assertSame([], $this->storedSettings());

        $client->request('GET', self::DETAILS_ROUTE, [], [], $headers);
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertNull($data['email']);
        self::assertNull($data['phone']);
        self::assertSame('default', $data['phone_source']);
    }

    public function testInvalidPhoneReturns422AndWritesNothing(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('PUT', self::DETAILS_ROUTE, [], [], $this->jsonHeaders($this->authHeaders()), json_encode([
            'email' => 'hola@example.com',
            'phone' => 'llámame',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
        self::assertSame([], $this->storedSettings());
    }

    public function testContactDetailsMissingFieldsAreTreatedAsEmpty(): void
    {
        $this->createAdminUser();
        $this->persist(new Setting(SettingKey::CONTACT_PHONE, '+34 629 991 659'));

        $client = $this->client();
        $client->request('PUT', self::DETAILS_ROUTE, [], [], $this->jsonHeaders($this->authHeaders()), '{}');

        self::assertResponseIsSuccessful();
        self::assertSame([], $this->storedSettings(), 'Un PUT sin campos debe limpiar el ajuste');
    }

    // ── Apoyo ─────────────────────────────────────────────────────────────

    /** @param array<string, string> $headers */
    private function jsonHeaders(array $headers = []): array
    {
        return ['CONTENT_TYPE' => 'application/json'] + $headers;
    }

    /**
     * Valor por defecto del .env que recibe el resolver: se lee del contenedor
     * en vez de getenv() para que el test compruebe el valor real en uso y no
     * dependa de cómo esté arrancado el proceso.
     */
    private function defaultEmail(): string
    {
        return (string) static::getContainer()->getParameter('mailer.to_email');
    }

    private function defaultName(): string
    {
        return (string) static::getContainer()->getParameter('mailer.to_name');
    }

    /** @return array<string, string> */
    private function storedSettings(): array
    {
        $stored = [];
        foreach ($this->em()->getRepository(Setting::class)->findAll() as $setting) {
            $stored[$setting->getKey()] = $setting->getValue();
        }

        return $stored;
    }
}

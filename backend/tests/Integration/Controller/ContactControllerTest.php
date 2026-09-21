<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\ContactSubmission;
use App\Tests\Integration\IntegrationTestCase;

final class ContactControllerTest extends IntegrationTestCase
{
    public function testSubmitsValidContact(): void
    {
        $client = $this->client();
        $client->request('POST', '/api/contact', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'María',
            'email' => 'maria@example.com',
            'message' => 'Quiero un presupuesto',
            'phone' => '+34 600 000 000',
            'eventType' => 'boda',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(201);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertArrayHasKey('message', $data);

        $saved = $this->em()->getRepository(ContactSubmission::class)->findAll();
        self::assertCount(1, $saved);
        self::assertSame('María', $saved[0]->getName());
        self::assertSame('+34 600 000 000', $saved[0]->getPhone());
        self::assertSame('boda', $saved[0]->getEventType());
        // NullMailer (services_test.yaml) nunca falla → email marcado como enviado
        self::assertTrue($saved[0]->isEmailSent());
    }

    public function testReturns422WithErrorsWhenInvalid(): void
    {
        $client = $this->client();
        $client->request('POST', '/api/contact', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => '',
            'email' => 'no-es-un-email',
            'message' => '',
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertArrayHasKey('name', $data['errors']);
        self::assertArrayHasKey('email', $data['errors']);
        self::assertArrayHasKey('message', $data['errors']);
        self::assertCount(0, $this->em()->getRepository(ContactSubmission::class)->findAll());
    }

    public function testRejectsMessageOver2000Chars(): void
    {
        $client = $this->client();
        $client->request('POST', '/api/contact', [], [], [
            'CONTENT_TYPE' => 'application/json',
        ], json_encode([
            'name' => 'María',
            'email' => 'maria@example.com',
            'message' => str_repeat('a', 2001),
        ], JSON_THROW_ON_ERROR));

        self::assertResponseStatusCodeSame(422);
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertArrayHasKey('message', $data['errors']);
    }
}

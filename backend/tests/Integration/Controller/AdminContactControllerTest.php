<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Entity\ContactSubmission;
use App\Tests\Integration\IntegrationTestCase;
use App\Tests\Support\TestFactory;

final class AdminContactControllerTest extends IntegrationTestCase
{
    // ── Auth ──────────────────────────────────────────────────────────────

    public function testRejectsRequestsWithoutToken(): void
    {
        $client = $this->client();
        $client->request('GET', '/api/admin/messages');

        self::assertResponseStatusCodeSame(401);
    }

    // ── Listado ───────────────────────────────────────────────────────────

    public function testListsMessagesWithUnreadCount(): void
    {
        $this->createAdminUser();
        $read = TestFactory::contactSubmission(name: 'Leído');
        $read->markRead();
        $this->persist(
            TestFactory::contactSubmission(name: 'María'),
            TestFactory::contactSubmission(name: 'Juan'),
            $read,
        );

        $client = $this->client();
        $client->request('GET', '/api/admin/messages', [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertCount(3, $data['items']);
        self::assertSame(3, $data['total']);
        self::assertSame(2, $data['unreadCount']);
        self::assertSame(1, $data['totalPages']);
        self::assertSame(1, $data['page']);
    }

    public function testPaginatesServerSide(): void
    {
        $this->createAdminUser();
        foreach (range(1, 21) as $i) {
            $this->persist(TestFactory::contactSubmission(name: "Mensaje $i"));
        }

        $client = $this->client();
        $client->request('GET', '/api/admin/messages?page=2', [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertCount(1, $data['items']);
        self::assertSame(2, $data['totalPages']);
        self::assertSame(21, $data['total']);
    }

    public function testInvalidPageReturns422(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('GET', '/api/admin/messages?page=0', [], [], $this->authHeaders());

        self::assertResponseStatusCodeSame(422);
    }

    // ── Detalle ───────────────────────────────────────────────────────────

    public function testGetsMessageDetail(): void
    {
        $this->createAdminUser();
        $sub = TestFactory::contactSubmission(name: 'María', ipAddress: '127.0.0.1');
        $this->persist($sub);

        $client = $this->client();
        $client->request('GET', '/api/admin/messages/' . $sub->getId(), [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);

        self::assertSame('María', $data['name']);
        self::assertSame('127.0.0.1', $data['ip_address']);
        self::assertSame('Hola, quiero un presupuesto', $data['message']);
        self::assertFalse($data['is_read']);
        self::assertNull($data['read_at']);
        self::assertFalse($data['email_sent']);
    }

    public function testUnknownMessageReturns404(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('GET', '/api/admin/messages/no-existe', [], [], $this->authHeaders());

        self::assertResponseStatusCodeSame(404);
    }

    // ── Leído / no leído ──────────────────────────────────────────────────

    public function testMarksReadAndUnread(): void
    {
        $this->createAdminUser();
        $sub = TestFactory::contactSubmission();
        $this->persist($sub);
        $id = $sub->getId();

        $client = $this->client();
        $client->request('POST', "/api/admin/messages/$id/read", [], [], $this->authHeaders());
        self::assertResponseIsSuccessful();

        $saved = $this->em()->getRepository(ContactSubmission::class)->find($id);
        self::assertTrue($saved->isRead());

        $client->request('POST', "/api/admin/messages/$id/unread", [], [], $this->authHeaders());
        self::assertResponseIsSuccessful();

        $saved = $this->em()->getRepository(ContactSubmission::class)->find($id);
        self::assertFalse($saved->isRead());
    }

    public function testMarkReadOnUnknownMessageReturns404(): void
    {
        $this->createAdminUser();

        $client = $this->client();
        $client->request('POST', '/api/admin/messages/no-existe/read', [], [], $this->authHeaders());

        self::assertResponseStatusCodeSame(404);
    }

    // ── Borrado ───────────────────────────────────────────────────────────

    public function testDeletesMessage(): void
    {
        $this->createAdminUser();
        $sub = TestFactory::contactSubmission();
        $this->persist($sub);
        $id = $sub->getId();

        $client = $this->client();
        $client->request('DELETE', "/api/admin/messages/$id", [], [], $this->authHeaders());

        self::assertResponseIsSuccessful();
        self::assertNull($this->em()->getRepository(ContactSubmission::class)->find($id));
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Integration\Controller;

use App\Tests\Integration\IntegrationTestCase;

final class HealthControllerTest extends IntegrationTestCase
{
    public function testHealthReturnsOk(): void
    {
        $client = $this->client();
        $client->request('GET', '/health');

        self::assertResponseIsSuccessful();
        $data = json_decode((string) $client->getResponse()->getContent(), true);
        self::assertSame('ok', $data['status']);
        self::assertArrayHasKey('timestamp', $data);
    }
}

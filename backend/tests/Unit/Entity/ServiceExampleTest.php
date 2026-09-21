<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\ServiceExample;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class ServiceExampleTest extends TestCase
{
    public function testConstructorGeneratesRandomHexId(): void
    {
        $service = TestFactory::service();
        $example = new ServiceExample($service, 'Título', 'Desc', 'https://x.test/f.jpg');

        self::assertMatchesRegularExpression('/^[0-9a-f]{32}$/', $example->getId());
        self::assertSame($service, $example->getService());
        self::assertSame('Título', $example->getTitle());
        self::assertSame('Desc', $example->getDescription());
        self::assertSame('https://x.test/f.jpg', $example->getImageUrl());
        self::assertSame(0, $example->getSortOrder());
    }

    public function testUpdateChangesFields(): void
    {
        $example = TestFactory::example(TestFactory::service());
        $example->update('Nuevo título', 'Nueva desc', 'https://x.test/nueva.jpg');

        self::assertSame('Nuevo título', $example->getTitle());
        self::assertSame('Nueva desc', $example->getDescription());
        self::assertSame('https://x.test/nueva.jpg', $example->getImageUrl());
    }

    public function testToArrayShape(): void
    {
        $example = TestFactory::example(TestFactory::service(), title: 'Foto', imageUrl: 'https://x.test/f.jpg');

        $data = $example->toArray();

        self::assertSame($example->getId(), $data['id']);
        self::assertSame('Foto', $data['title']);
        self::assertSame('Descripción de la foto', $data['description']);
        self::assertSame('https://x.test/f.jpg', $data['image']);
    }
}

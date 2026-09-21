<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Service;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class ServiceTest extends TestCase
{
    public function testConstructorSetsDefaults(): void
    {
        $service = new Service('id', 'Nombre', '🎉', 'Desc', ['A'], 'food');

        self::assertSame('id', $service->getId());
        self::assertSame(0, $service->getSortOrder());
        self::assertTrue($service->isActive());
        self::assertSame([], $service->getExamples()->toArray());
    }

    public function testUpdateChangesEditableFields(): void
    {
        $service = TestFactory::service();
        $service->update('Nuevo', '🍭', 'Nueva desc', ['X'], 'animacion');

        self::assertSame('Nuevo', $service->getName());
        self::assertSame('🍭', $service->getEmoji());
        self::assertSame('Nueva desc', $service->getDescription());
        self::assertSame(['X'], $service->getFeatures());
        self::assertSame('animacion', $service->getCategory());
    }

    public function testActivateAndDeactivate(): void
    {
        $service = TestFactory::service();
        $service->deactivate();
        self::assertFalse($service->isActive());

        $service->activate();
        self::assertTrue($service->isActive());
    }

    public function testToArrayIncludesExamples(): void
    {
        $service = TestFactory::service();
        TestFactory::attachExamples($service, TestFactory::example($service, title: 'Foto'));

        $data = $service->toArray();

        self::assertSame('servicio-test', $data['id']);
        self::assertSame('🎉', $data['emoji']);
        self::assertCount(1, $data['examples']);
        self::assertSame('Foto', $data['examples'][0]['title']);
    }
}

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
        self::assertSame('https://fake-storage.test/services/abc.jpg', $data['image']);
        self::assertCount(1, $data['examples']);
        self::assertSame('Foto', $data['examples'][0]['title']);
    }

    public function testImageUrlKeepedAndCleared(): void
    {
        $service = TestFactory::service(imageUrl: 'https://fake-storage.test/services/propia.jpg');
        self::assertSame('https://fake-storage.test/services/propia.jpg', $service->getImageUrl());

        $service->setImageUrl(null);
        self::assertNull($service->getImageUrl());
    }

    public function testDisplayImageUsesOwnImageBeforeGallery(): void
    {
        $service = TestFactory::service(imageUrl: 'https://fake-storage.test/services/propia.jpg');
        TestFactory::attachExamples(
            $service,
            TestFactory::example($service, imageUrl: 'https://fake-storage.test/services/galeria.jpg'),
        );

        self::assertSame('https://fake-storage.test/services/propia.jpg', $service->getDisplayImage());
    }

    public function testDisplayImageFallsBackToFirstGalleryPhoto(): void
    {
        $service = TestFactory::service();
        TestFactory::attachExamples(
            $service,
            TestFactory::example($service, imageUrl: 'https://fake-storage.test/services/primera.jpg'),
            TestFactory::example($service, imageUrl: 'https://fake-storage.test/services/segunda.jpg'),
        );

        self::assertSame('https://fake-storage.test/services/primera.jpg', $service->getDisplayImage());
    }

    public function testDisplayImageIsNullWithoutOwnImageNorGallery(): void
    {
        self::assertNull(TestFactory::service()->getDisplayImage());
    }
}

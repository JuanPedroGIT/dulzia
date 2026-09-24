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

    public function testFeaturedIsOffByDefaultAndCanBeChanged(): void
    {
        // Una sección nueva no sale en la portada salvo que se marque en el panel.
        $service = TestFactory::service();
        self::assertFalse($service->isFeatured());

        $service->setFeatured(true);
        self::assertTrue($service->isFeatured());

        $service->setFeatured(false);
        self::assertFalse($service->isFeatured());
    }

    public function testToArrayIncludesFeatured(): void
    {
        $service = TestFactory::service(isFeatured: true);

        self::assertTrue($service->toArray()['featured']);
        self::assertFalse(TestFactory::service()->toArray()['featured']);
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

    public function testThumbnailUrlKeepedAndCleared(): void
    {
        $service = TestFactory::service(imageUrl: 'https://x.test/propia.jpg', thumbnailUrl: 'https://x.test/mini.jpg');
        self::assertSame('https://x.test/mini.jpg', $service->getThumbnailUrl());

        $service->setThumbnailUrl(null);
        self::assertNull($service->getThumbnailUrl());
        // Sin miniatura propia, la lista cae a la foto grande.
        self::assertSame('https://x.test/propia.jpg', $service->getDisplayThumbnail());
    }

    public function testDisplayThumbnailUsesOwnImageBeforeGallery(): void
    {
        $service = TestFactory::service(imageUrl: 'https://x.test/propia.jpg');
        TestFactory::attachExamples(
            $service,
            TestFactory::example(
                $service,
                imageUrl: 'https://x.test/galeria.jpg',
                thumbnailUrl: 'https://x.test/galeria-mini.jpg',
            ),
        );

        self::assertSame('https://x.test/propia.jpg', $service->getDisplayThumbnail());
    }

    public function testDisplayThumbnailFallsBackToGalleryThumbnail(): void
    {
        $service = TestFactory::service();
        TestFactory::attachExamples(
            $service,
            TestFactory::example(
                $service,
                imageUrl: 'https://x.test/primera.jpg',
                thumbnailUrl: 'https://x.test/primera-mini.jpg',
            ),
        );

        self::assertSame('https://x.test/primera-mini.jpg', $service->getDisplayThumbnail());
        self::assertSame('https://x.test/primera.jpg', $service->getDisplayImage());
    }

    public function testDisplayThumbnailIsNullWithoutAnyImage(): void
    {
        self::assertNull(TestFactory::service()->getDisplayThumbnail());
    }

    public function testToArrayIncludesThumbnail(): void
    {
        $service = TestFactory::service(imageUrl: 'https://x.test/propia.jpg', thumbnailUrl: 'https://x.test/mini.jpg');

        $data = $service->toArray();

        self::assertSame('https://x.test/propia.jpg', $data['image']);
        self::assertSame('https://x.test/mini.jpg', $data['thumbnail']);
    }
}

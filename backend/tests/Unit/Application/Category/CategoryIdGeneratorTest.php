<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Category;

use App\Application\Category\CreateCategory\CategoryIdGenerator;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Entity\Category;
use PHPUnit\Framework\TestCase;

final class CategoryIdGeneratorTest extends TestCase
{
    private function generator(?Category $existing): CategoryIdGenerator
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $repo->method('findById')->willReturn($existing);

        return new CategoryIdGenerator($repo);
    }

    public function testSlugifiesTheName(): void
    {
        self::assertSame('animacion-infantil', $this->generator(null)->generate('Animación Infantil'));
        self::assertSame('mesa-dulce', $this->generator(null)->generate('  Mesa dulce  '));
        self::assertSame('algodon-de-azucar', $this->generator(null)->generate('Algodón de Azúcar'));
        self::assertSame('animacion', $this->generator(null)->generate('ANIMACIÓN'));
    }

    public function testAppendsSuffixWhenIdAlreadyExists(): void
    {
        $id = $this->generator(new Category('animacion', 'Animación'))->generate('Animación');

        self::assertMatchesRegularExpression('/^animacion-\d{4}$/', $id);
    }
}

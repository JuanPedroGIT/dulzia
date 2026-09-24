<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    public function testConstructorSetsFields(): void
    {
        $category = new Category('food', 'Gastronomía', '🍴', 2);

        self::assertSame('food', $category->getId());
        self::assertSame('Gastronomía', $category->getName());
        self::assertSame('🍴', $category->getEmoji());
        self::assertSame(2, $category->getSortOrder());
    }

    public function testEmojiAndOrderAreOptional(): void
    {
        $category = new Category('food', 'Gastronomía');

        self::assertNull($category->getEmoji());
        self::assertSame(0, $category->getSortOrder());
    }

    public function testUpdateChangesEditableFieldsButNotTheId(): void
    {
        $category = new Category('food', 'Gastronomía', '🍴', 0);
        $category->update('Comida', null, 5);

        // El id no se toca: los servicios lo referencian.
        self::assertSame('food', $category->getId());
        self::assertSame('Comida', $category->getName());
        self::assertNull($category->getEmoji());
        self::assertSame(5, $category->getSortOrder());
    }

    public function testToArrayShape(): void
    {
        $category = new Category('food', 'Gastronomía', '🍴', 1);

        self::assertSame(
            ['id' => 'food', 'name' => 'Gastronomía', 'emoji' => '🍴', 'sort_order' => 1],
            $category->toArray(),
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Category;

use App\Application\Category\UpdateCategory\UpdateCategoryCommand;
use App\Application\Category\UpdateCategory\UpdateCategoryHandler;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Entity\Category;
use PHPUnit\Framework\TestCase;

final class UpdateCategoryHandlerTest extends TestCase
{
    public function testUpdatesEditableFields(): void
    {
        $category = new Category('food', 'Gastronomía', '🍴', 0);

        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $repo->method('findById')->willReturn($category);
        $repo->expects($this->once())->method('save')->with($category);

        (new UpdateCategoryHandler($repo))->handle(new UpdateCategoryCommand('food', 'Comida', '🍕', 7));

        self::assertSame('food', $category->getId());
        self::assertSame('Comida', $category->getName());
        self::assertSame('🍕', $category->getEmoji());
        self::assertSame(7, $category->getSortOrder());
    }

    public function testThrowsWhenCategoryNotFound(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->never())->method('save');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Categoría no encontrada');

        (new UpdateCategoryHandler($repo))->handle(new UpdateCategoryCommand('no-existe', 'X', null, 0));
    }
}

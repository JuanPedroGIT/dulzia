<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Category;

use App\Application\Category\CreateCategory\CreateCategoryCommand;
use App\Application\Category\CreateCategory\CreateCategoryHandler;
use App\Application\Category\CreateCategory\CategoryIdGenerator;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Entity\Category;
use PHPUnit\Framework\TestCase;

final class CreateCategoryHandlerTest extends TestCase
{
    public function testCreatesCategoryWithSlugId(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Category $category): bool {
                self::assertSame('animacion', $category->getId());
                self::assertSame('Animación', $category->getName());
                self::assertSame('🎪', $category->getEmoji());
                self::assertSame(3, $category->getSortOrder());

                return true;
            }));

        $result = (new CreateCategoryHandler($repo, new CategoryIdGenerator($repo)))
            ->handle(new CreateCategoryCommand('Animación', '🎪', 3));

        self::assertSame('animacion', $result['id']);
        self::assertSame('Animación', $result['name']);
    }

    public function testCreatesCategoryWithoutEmoji(): void
    {
        $repo = $this->createMock(CategoryRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->once())
            ->method('save')
            ->with($this->callback(function (Category $category): bool {
                self::assertNull($category->getEmoji());

                return true;
            }));

        (new CreateCategoryHandler($repo, new CategoryIdGenerator($repo)))
            ->handle(new CreateCategoryCommand('Animación', null, 0));
    }
}

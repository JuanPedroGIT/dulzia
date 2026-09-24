<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Category;

use App\Application\Category\DeleteCategory\DeleteCategoryCommand;
use App\Application\Category\DeleteCategory\DeleteCategoryHandler;
use App\Domain\Category\CategoryInUseException;
use App\Domain\Category\CategoryRepositoryInterface;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Entity\Category;
use PHPUnit\Framework\TestCase;

final class DeleteCategoryHandlerTest extends TestCase
{
    private function handler(CategoryRepositoryInterface $categories, int $inUse): DeleteCategoryHandler
    {
        $services = $this->createMock(ServiceRepositoryInterface::class);
        $services->method('countByCategory')->willReturn($inUse);

        return new DeleteCategoryHandler($categories, $services);
    }

    public function testDeletesWhenNoServiceUsesIt(): void
    {
        $category = new Category('animacion', 'Animación');

        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->method('findById')->willReturn($category);
        $categories->expects($this->once())->method('delete')->with($category);

        ($this->handler($categories, 0))->handle(new DeleteCategoryCommand('animacion'));
    }

    public function testBlocksDeletionWhenServicesUseIt(): void
    {
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->method('findById')->willReturn(new Category('food', 'Gastronomía'));
        $categories->expects($this->never())->method('delete');

        $this->expectException(CategoryInUseException::class);
        $this->expectExceptionMessage('No se puede borrar: la usan 3 secciones.');

        ($this->handler($categories, 3))->handle(new DeleteCategoryCommand('food'));
    }

    public function testMessageUsesSingularForOneService(): void
    {
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->method('findById')->willReturn(new Category('food', 'Gastronomía'));

        $this->expectException(CategoryInUseException::class);
        $this->expectExceptionMessage('No se puede borrar: la usan 1 sección.');

        ($this->handler($categories, 1))->handle(new DeleteCategoryCommand('food'));
    }

    public function testThrowsWhenCategoryNotFound(): void
    {
        $categories = $this->createMock(CategoryRepositoryInterface::class);
        $categories->method('findById')->willReturn(null);
        $categories->expects($this->never())->method('delete');

        $this->expectException(NotFoundException::class);

        ($this->handler($categories, 0))->handle(new DeleteCategoryCommand('no-existe'));
    }
}

<?php

namespace App\Controller;

use App\Application\Category\CategoryCommandFactory;
use App\Application\Category\CreateCategory\CreateCategoryHandler;
use App\Application\Category\DeleteCategory\DeleteCategoryCommand;
use App\Application\Category\DeleteCategory\DeleteCategoryHandler;
use App\Application\Category\ListCategories\ListCategoriesHandler;
use App\Application\Category\ListCategories\ListCategoriesQuery;
use App\Application\Category\UpdateCategory\UpdateCategoryHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * CRUD de categorías del panel (un solo agregado: Category).
 *
 * - Autenticación: AdminAuthListener.
 * - Errores: ApiExceptionListener (NotFoundException → 404, InvalidInputException → 400,
 *   CategoryInUseException → 409).
 * - El parseo del body vive en CategoryCommandFactory.
 */
final class AdminCategoryController
{
    public function __construct(
        private ListCategoriesHandler $listCategories,
        private CreateCategoryHandler $createCategory,
        private UpdateCategoryHandler $updateCategory,
        private DeleteCategoryHandler $deleteCategory,
        private CategoryCommandFactory $commands,
    ) {}

    #[Route('/api/admin/categories', methods: ['GET'])]
    public function listCategories(): JsonResponse
    {
        // Con el uso de cada una: el panel avisa antes de borrar.
        return new JsonResponse(
            $this->listCategories->handle(new ListCategoriesQuery(withServiceCount: true)),
        );
    }

    #[Route('/api/admin/categories', methods: ['POST'])]
    public function createCategory(Request $request): JsonResponse
    {
        return new JsonResponse(
            $this->createCategory->handle($this->commands->createFromRequest($request)),
            201,
        );
    }

    #[Route('/api/admin/categories/{id}', methods: ['PUT', 'POST'])]
    public function updateCategory(string $id, Request $request): JsonResponse
    {
        $this->updateCategory->handle($this->commands->updateFromRequest($id, $request));

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/admin/categories/{id}', methods: ['DELETE'])]
    public function deleteCategory(string $id): JsonResponse
    {
        $this->deleteCategory->handle(new DeleteCategoryCommand($id));

        return new JsonResponse(['ok' => true]);
    }
}

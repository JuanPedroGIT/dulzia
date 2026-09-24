<?php

namespace App\Controller;

use App\Application\Category\ListCategories\ListCategoriesHandler;
use App\Application\Category\ListCategories\ListCategoriesQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Categorías del catálogo público: las pestañas y las etiquetas de la web.
 */
final class CategoryController
{
    public function __construct(
        private ListCategoriesHandler $list,
    ) {}

    #[Route('/api/categories', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return new JsonResponse($this->list->handle(new ListCategoriesQuery()));
    }
}

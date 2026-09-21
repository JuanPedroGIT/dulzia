<?php

namespace App\Controller;

use App\Application\Service\GetCatalogService\GetCatalogServiceHandler;
use App\Application\Service\GetCatalogService\GetCatalogServiceQuery;
use App\Application\Service\ListCatalogServices\ListCatalogServicesHandler;
use App\Application\Service\ListCatalogServices\ListCatalogServicesQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController
{
    public function __construct(
        private ListCatalogServicesHandler $list,
        private GetCatalogServiceHandler $get,
    ) {}

    #[Route('/api/services', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $services = $this->list->handle(new ListCatalogServicesQuery());

        $response = new JsonResponse($services);

        // Forzar keep-alive y evitar chunked encoding
        $response->headers->set('Connection', 'keep-alive');
        $response->headers->set('Content-Length', strlen($response->getContent()));

        return $response;
    }

    #[Route('/api/services/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $service = $this->get->handle(new GetCatalogServiceQuery($id));

        if ($service === null) {
            return new JsonResponse(['error' => 'Servicio no encontrado'], 404);
        }

        return new JsonResponse($service);
    }
}

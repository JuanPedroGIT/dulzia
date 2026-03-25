<?php

namespace App\Controller;

use App\Domain\Service\ServiceRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class ServiceController
{
    public function __construct(
        private ServiceRepositoryInterface $repository,
    ) {}

    #[Route('/api/services', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $services = $this->repository->findAllActive();

        return new JsonResponse(
            array_map(fn($s) => $s->toArray(), $services)
        );
    }

    #[Route('/api/services/{id}', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        $service = $this->repository->findById($id);

        if ($service === null || !$service->isActive()) {
            return new JsonResponse(['error' => 'Servicio no encontrado'], 404);
        }

        return new JsonResponse($service->toArray());
    }
}

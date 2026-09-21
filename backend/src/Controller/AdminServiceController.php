<?php

namespace App\Controller;

use App\Application\Service\ActivateService\ActivateServiceCommand;
use App\Application\Service\ActivateService\ActivateServiceHandler;
use App\Application\Service\CreateService\CreateServiceHandler;
use App\Application\Service\DeleteService\DeleteServiceCommand;
use App\Application\Service\DeleteService\DeleteServiceHandler;
use App\Application\Service\GetService\GetServiceHandler;
use App\Application\Service\GetService\GetServiceQuery;
use App\Application\Service\ListServices\ListServicesHandler;
use App\Application\Service\ListServices\ListServicesQuery;
use App\Application\Service\ServiceCommandFactory;
use App\Application\Service\UpdateService\UpdateServiceHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * CRUD de servicios del panel admin (un solo agregado: Service).
 *
 * - Autenticación: AdminAuthListener.
 * - Errores: ApiExceptionListener (NotFoundException → 404, InvalidInputException → 400).
 * - El parseo del body JSON vive en ServiceCommandFactory.
 */
final class AdminServiceController
{
    public function __construct(
        private ListServicesHandler $listServices,
        private GetServiceHandler $getService,
        private CreateServiceHandler $createService,
        private UpdateServiceHandler $updateService,
        private DeleteServiceHandler $deleteService,
        private ActivateServiceHandler $activateService,
        private ServiceCommandFactory $commands,
    ) {}

    #[Route('/api/admin/services', methods: ['GET'])]
    public function listServices(): JsonResponse
    {
        return new JsonResponse(
            $this->listServices->handle(new ListServicesQuery()),
        );
    }

    #[Route('/api/admin/services/{id}', methods: ['GET'])]
    public function getService(string $id): JsonResponse
    {
        $data = $this->getService->handle(new GetServiceQuery($id));

        if ($data === null) {
            return new JsonResponse(['error' => 'Servicio no encontrado'], 404);
        }

        return new JsonResponse($data);
    }

    #[Route('/api/admin/services', methods: ['POST'])]
    public function createService(Request $request): JsonResponse
    {
        $result = $this->createService->handle(
            $this->commands->createFromRequest($request),
        );

        return new JsonResponse($result, 201);
    }

    #[Route('/api/admin/services/{id}', methods: ['PUT'])]
    public function updateService(string $id, Request $request): JsonResponse
    {
        $this->updateService->handle(
            $this->commands->updateFromRequest($id, $request),
        );

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/admin/services/{id}', methods: ['DELETE'])]
    public function deactivateService(string $id): JsonResponse
    {
        $this->deleteService->handle(new DeleteServiceCommand($id));

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/admin/services/{id}/activate', methods: ['POST'])]
    public function activateService(string $id): JsonResponse
    {
        $this->activateService->handle(new ActivateServiceCommand($id));

        return new JsonResponse(['ok' => true]);
    }
}

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
use App\Application\Service\SetFeatured\SetFeaturedCommand;
use App\Application\Service\SetFeatured\SetFeaturedHandler;
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
        private SetFeaturedHandler $setFeatured,
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

    /**
     * PUT (JSON) se mantiene por compatibilidad; el panel usa POST porque envía
     * multipart con la foto de la sección y PHP solo rellena $_POST/$_FILES en
     * POST (mismo motivo por el que la edición de fotos ya usa POST).
     */
    #[Route('/api/admin/services/{id}', methods: ['PUT', 'POST'])]
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

    /**
     * Destacado en la portada. Un solo endpoint idempotente con el estado
     * deseado en lugar de dos rutas (feature/unfeature): el panel manda lo que
     * quiere que valga, y repetirlo no cambia nada.
     */
    #[Route('/api/admin/services/{id}/featured', methods: ['POST'])]
    public function setFeatured(string $id, Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        $this->setFeatured->handle(new SetFeaturedCommand(
            id: $id,
            featured: filter_var($body['featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ));

        return new JsonResponse(['ok' => true]);
    }
}

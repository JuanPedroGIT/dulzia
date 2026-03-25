<?php

namespace App\Controller;

use App\Application\Service\ActivateService\ActivateServiceCommand;
use App\Application\Service\ActivateService\ActivateServiceHandler;
use App\Application\Service\AddPhoto\AddPhotoCommand;
use App\Application\Service\AddPhoto\AddPhotoHandler;
use App\Application\Service\CreateService\CreateServiceCommand;
use App\Application\Service\CreateService\CreateServiceHandler;
use App\Application\Service\DeletePhoto\DeletePhotoCommand;
use App\Application\Service\DeletePhoto\DeletePhotoHandler;
use App\Application\Service\DeleteService\DeleteServiceCommand;
use App\Application\Service\DeleteService\DeleteServiceHandler;
use App\Application\Service\GetService\GetServiceQuery;
use App\Application\Service\GetService\GetServiceHandler;
use App\Application\Service\ListServices\ListServicesQuery;
use App\Application\Service\ListServices\ListServicesHandler;
use App\Application\Service\UpdatePhoto\UpdatePhotoCommand;
use App\Application\Service\UpdatePhoto\UpdatePhotoHandler;
use App\Application\Service\UpdateService\UpdateServiceCommand;
use App\Application\Service\UpdateService\UpdateServiceHandler;
use App\Security\AdminTokenService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AdminServiceController
{
    public function __construct(
        private AdminTokenService     $tokenService,
        private ListServicesHandler   $listServices,
        private GetServiceHandler     $getService,
        private CreateServiceHandler  $createService,
        private UpdateServiceHandler  $updateService,
        private DeleteServiceHandler  $deleteService,
        private ActivateServiceHandler $activateService,
        private AddPhotoHandler       $addPhoto,
        private UpdatePhotoHandler    $updatePhoto,
        private DeletePhotoHandler    $deletePhoto,
    ) {}

    // ── Servicios ────────────────────────────────────────────────────────

    #[Route('/api/admin/services', methods: ['GET'])]
    public function listServices(Request $request): JsonResponse
    {
        $this->auth($request);

        return new JsonResponse(
            $this->listServices->handle(new ListServicesQuery())
        );
    }

    #[Route('/api/admin/services/{id}', methods: ['GET'])]
    public function getService(string $id, Request $request): JsonResponse
    {
        $this->auth($request);

        $data = $this->getService->handle(new GetServiceQuery($id));

        if ($data === null) {
            return new JsonResponse(['error' => 'Servicio no encontrado'], 404);
        }

        return new JsonResponse($data);
    }

    #[Route('/api/admin/services', methods: ['POST'])]
    public function createService(Request $request): JsonResponse
    {
        $this->auth($request);

        $data        = json_decode($request->getContent(), true) ?? [];
        $name        = trim($data['name'] ?? '');
        $emoji       = trim($data['emoji'] ?? '');
        $description = trim($data['description'] ?? '');
        $features    = array_values(array_filter(array_map('trim', $data['features'] ?? [])));
        $category    = $data['category'] ?? 'food';

        if ($name === '' || $emoji === '' || $description === '') {
            return new JsonResponse(['error' => 'name, emoji y description son requeridos'], 400);
        }

        $result = $this->createService->handle(
            new CreateServiceCommand($name, $emoji, $description, $features, $category)
        );

        return new JsonResponse($result, 201);
    }

    #[Route('/api/admin/services/{id}', methods: ['PUT'])]
    public function updateService(string $id, Request $request): JsonResponse
    {
        $this->auth($request);

        $data        = json_decode($request->getContent(), true) ?? [];
        $name        = trim($data['name'] ?? '');
        $emoji       = trim($data['emoji'] ?? '');
        $description = trim($data['description'] ?? '');
        $features    = array_values(array_filter(array_map('trim', $data['features'] ?? [])));
        $category    = $data['category'] ?? 'food';

        if ($name === '' || $emoji === '' || $description === '') {
            return new JsonResponse(['error' => 'name, emoji y description son requeridos'], 400);
        }

        try {
            $this->updateService->handle(
                new UpdateServiceCommand($id, $name, $emoji, $description, $features, $category)
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/admin/services/{id}', methods: ['DELETE'])]
    public function deactivateService(string $id, Request $request): JsonResponse
    {
        $this->auth($request);

        try {
            $this->deleteService->handle(new DeleteServiceCommand($id));
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/admin/services/{id}/activate', methods: ['POST'])]
    public function activateService(string $id, Request $request): JsonResponse
    {
        $this->auth($request);

        try {
            $this->activateService->handle(new ActivateServiceCommand($id));
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse(['ok' => true]);
    }

    // ── Fotos ────────────────────────────────────────────────────────────

    #[Route('/api/admin/services/{serviceId}/photos', methods: ['POST'])]
    public function addPhoto(string $serviceId, Request $request): JsonResponse
    {
        $this->auth($request);

        $title       = $request->request->get('title', '');
        $description = $request->request->get('description', '');
        $imageUrl    = $request->request->get('imageUrl', '');

        if ($title === '' || $description === '') {
            return new JsonResponse(['error' => 'title y description son requeridos'], 400);
        }

        $file = $request->files->get('image');
        if ($file !== null && !$file->isValid()) {
            return new JsonResponse(['error' => 'Archivo inválido: ' . $file->getErrorMessage()], 400);
        }

        try {
            $result = $this->addPhoto->handle(new AddPhotoCommand(
                serviceId:   $serviceId,
                title:       $title,
                description: $description,
                imageUrl:    $imageUrl,
                file:        $request->files->get('image'),
            ));
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse($result, 201);
    }

    #[Route('/api/admin/photos/{photoId}', methods: ['POST'])]
    public function updatePhoto(string $photoId, Request $request): JsonResponse
    {
        $this->auth($request);

        $file = $request->files->get('image');
        if ($file !== null && !$file->isValid()) {
            return new JsonResponse(['error' => 'Archivo inválido: ' . $file->getErrorMessage()], 400);
        }

        try {
            $result = $this->updatePhoto->handle(new UpdatePhotoCommand(
                photoId:     $photoId,
                title:       $request->request->get('title'),
                description: $request->request->get('description'),
                imageUrl:    $request->request->get('imageUrl'),
                file:        $request->files->get('image'),
            ));
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }

        return new JsonResponse($result);
    }

    #[Route('/api/admin/photos/{photoId}', methods: ['DELETE'])]
    public function deletePhoto(string $photoId, Request $request): JsonResponse
    {
        $this->auth($request);

        try {
            $this->deletePhoto->handle(new DeletePhotoCommand($photoId));
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], 404);
        }

        return new JsonResponse(['ok' => true]);
    }

    // ── Auth ─────────────────────────────────────────────────────────────

    private function auth(Request $request): void
    {
        try {
            $this->tokenService->validateRequest($request);
        } catch (\RuntimeException) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(401, 'No autorizado');
        }
    }
}

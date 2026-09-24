<?php

namespace App\Controller;

use App\Application\Service\AddPhoto\AddPhotoCommand;
use App\Application\Service\AddPhoto\AddPhotoHandler;
use App\Application\Service\DeletePhoto\DeletePhotoCommand;
use App\Application\Service\DeletePhoto\DeletePhotoHandler;
use App\Application\Service\UpdatePhoto\UpdatePhotoCommand;
use App\Application\Service\UpdatePhoto\UpdatePhotoHandler;
use App\Domain\Storage\InvalidFileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Gestión de fotos del panel admin (un solo agregado: ServiceExample).
 *
 * - Autenticación: AdminAuthListener.
 * - Errores: ApiExceptionListener (NotFoundException → 404, InvalidFileException → 400).
 */
final class AdminPhotoController
{
    public function __construct(
        private AddPhotoHandler $addPhoto,
        private UpdatePhotoHandler $updatePhoto,
        private DeletePhotoHandler $deletePhoto,
    ) {}

    #[Route('/api/admin/services/{serviceId}/photos', methods: ['POST'])]
    public function addPhoto(string $serviceId, Request $request): JsonResponse
    {
        $title = $request->request->get('title', '');
        $description = $request->request->get('description', '');

        if ($title === '' || $description === '') {
            return new JsonResponse(['error' => 'title y description son requeridos'], 400);
        }

        $result = $this->addPhoto->handle(new AddPhotoCommand(
            serviceId: $serviceId,
            title: $title,
            description: $description,
            imageUrl: $request->request->get('imageUrl', ''),
            file: $this->uploadedFile($request, 'image'),
            thumbnail: $this->uploadedFile($request, 'thumbnail'),
        ));

        return new JsonResponse($result, 201);
    }

    #[Route('/api/admin/photos/{photoId}', methods: ['POST'])]
    public function updatePhoto(string $photoId, Request $request): JsonResponse
    {
        $result = $this->updatePhoto->handle(new UpdatePhotoCommand(
            photoId: $photoId,
            title: $request->request->get('title'),
            description: $request->request->get('description'),
            imageUrl: $request->request->get('imageUrl'),
            file: $this->uploadedFile($request, 'image'),
            thumbnail: $this->uploadedFile($request, 'thumbnail'),
        ));

        return new JsonResponse($result);
    }

    #[Route('/api/admin/photos/{photoId}', methods: ['DELETE'])]
    public function deletePhoto(string $photoId): JsonResponse
    {
        $this->deletePhoto->handle(new DeletePhotoCommand($photoId));

        return new JsonResponse(['ok' => true]);
    }

    /**
     * El fichero se valida aquí solo por el error de subida de PHP; el MIME y el
     * tamaño los valida el storage, que lanza InvalidFileException (400).
     */
    private function uploadedFile(Request $request, string $field): ?UploadedFile
    {
        $file = $request->files->get($field);

        if ($file !== null && !$file->isValid()) {
            throw new InvalidFileException('Archivo inválido: ' . $file->getErrorMessage());
        }

        return $file;
    }
}

<?php

namespace App\Controller;

use App\Application\Banner\BannerCommandFactory;
use App\Application\Banner\CreateBanner\CreateBannerHandler;
use App\Application\Banner\DeleteBanner\DeleteBannerCommand;
use App\Application\Banner\DeleteBanner\DeleteBannerHandler;
use App\Application\Banner\ListBanners\ListBannersHandler;
use App\Application\Banner\ListBanners\ListBannersQuery;
use App\Application\Banner\UpdateBanner\UpdateBannerHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * CRUD del banner informativo (un solo agregado: Banner).
 *
 * - Autenticación: AdminAuthListener.
 * - Errores: ApiExceptionListener (NotFoundException → 404,
 *   ValidationFailedException → 422).
 * - El parseo del body vive en BannerCommandFactory; la validación de las
 *   reglas, en los commands.
 */
final class AdminBannerController
{
    public function __construct(
        private ListBannersHandler $listBanners,
        private CreateBannerHandler $createBanner,
        private UpdateBannerHandler $updateBanner,
        private DeleteBannerHandler $deleteBanner,
        private BannerCommandFactory $commands,
        private ValidatorInterface $validator,
    ) {}

    #[Route('/api/admin/banners', methods: ['GET'])]
    public function listBanners(): JsonResponse
    {
        return new JsonResponse(
            $this->listBanners->handle(new ListBannersQuery()),
        );
    }

    #[Route('/api/admin/banners', methods: ['POST'])]
    public function createBanner(Request $request): JsonResponse
    {
        $command = $this->commands->createFromRequest($request);
        $this->validate($command);

        return new JsonResponse($this->createBanner->handle($command), 201);
    }

    #[Route('/api/admin/banners/{id}', methods: ['PUT', 'POST'])]
    public function updateBanner(string $id, Request $request): JsonResponse
    {
        $command = $this->commands->updateFromRequest($id, $request);
        $this->validate($command);

        $this->updateBanner->handle($command);

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/admin/banners/{id}', methods: ['DELETE'])]
    public function deleteBanner(string $id): JsonResponse
    {
        $this->deleteBanner->handle(new DeleteBannerCommand($id));

        return new JsonResponse(['ok' => true]);
    }

    private function validate(object $command): void
    {
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($command, $violations);
        }
    }
}

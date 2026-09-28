<?php

namespace App\Controller;

use App\Application\Banner\GetActiveBanner\GetActiveBannerHandler;
use App\Application\Banner\GetActiveBanner\GetActiveBannerQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

final class BannerController
{
    public function __construct(
        private GetActiveBannerHandler $getActiveBanner,
    ) {}

    /**
     * El banner que la web enseña arriba del todo. Público y sin token: es lo
     * mismo que ya se ve en el HTML prerenderizado.
     *
     * `banner` vale null cuando no hay ninguno en ventana: la web entonces no
     * pinta nada.
     */
    #[Route('/api/banner', methods: ['GET'])]
    public function activeBanner(): JsonResponse
    {
        return new JsonResponse(
            $this->getActiveBanner->handle(new GetActiveBannerQuery()),
        );
    }
}

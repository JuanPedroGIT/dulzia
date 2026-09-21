<?php

namespace App\EventListener;

use App\Domain\Admin\AdminTokenStoreInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Protege las rutas /api/admin (excepto el login) exigiendo un token válido.
 * Sustituye a la validación de token repetida en cada acción del controller.
 */
final class AdminAuthListener
{
    private const OPEN_ROUTE = 'app_adminauth_login';

    public function __construct(
        private AdminTokenStoreInterface $tokens,
    ) {}

    // Después de RouterListener (32) para tener _route resuelta
    #[AsEventListener(priority: 8)]
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api/admin')) {
            return;
        }

        if ($request->attributes->get('_route') === self::OPEN_ROUTE) {
            return;
        }

        $header = $request->headers->get('Authorization', '');
        if (!str_starts_with($header, 'Bearer ')) {
            throw new HttpException(401, 'No autorizado');
        }

        if (!$this->tokens->isValid(substr($header, 7))) {
            throw new HttpException(401, 'No autorizado');
        }
    }
}

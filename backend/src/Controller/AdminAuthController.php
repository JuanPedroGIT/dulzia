<?php

namespace App\Controller;

use App\Application\AdminAuth\Login\LoginCommand;
use App\Application\AdminAuth\Login\LoginHandler;
use App\Application\AdminAuth\Logout\LogoutCommand;
use App\Application\AdminAuth\Logout\LogoutHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AdminAuthController
{
    public function __construct(
        private LoginHandler $login,
        private LogoutHandler $logout,
    ) {}

    #[Route('/api/admin/login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        if ($username === '' || $password === '') {
            return new JsonResponse(['error' => 'Credenciales requeridas'], 400);
        }

        // InvalidCredentialsException → 401 JSON vía ApiExceptionListener
        $token = $this->login->handle(new LoginCommand($username, $password));

        return new JsonResponse(['token' => $token]);
    }

    // Protegida por AdminAuthListener (requiere token válido)
    #[Route('/api/admin/logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        $this->logout->handle(new LogoutCommand());

        return new JsonResponse(['ok' => true]);
    }

    // Protegida por AdminAuthListener: no valida nada por sí misma. Si llega aquí
    // es que el Bearer es válido; si no, el listener ya respondió 401 antes.
    #[Route('/api/admin/check', methods: ['GET'])]
    public function check(): JsonResponse
    {
        return new JsonResponse(['ok' => true]);
    }
}

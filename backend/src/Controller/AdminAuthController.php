<?php
namespace App\Controller;

use App\Security\AdminTokenService;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

final class AdminAuthController
{
    public function __construct(
        private Connection $connection,
        private AdminTokenService $tokenService,
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

        $admin = $this->connection->fetchAssociative(
            'SELECT password_hash FROM admin_user WHERE username = ?',
            [$username]
        );

        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            return new JsonResponse(['error' => 'Credenciales incorrectas'], 401);
        }

        $token = $this->tokenService->createToken();

        return new JsonResponse(['token' => $token]);
    }

    #[Route('/api/admin/logout', methods: ['POST'])]
    public function logout(Request $request): JsonResponse
    {
        try {
            $this->tokenService->validateRequest($request);
            $this->connection->executeStatement('DELETE FROM admin_token');
        } catch (\RuntimeException) {
            // already logged out
        }

        return new JsonResponse(['ok' => true]);
    }
}

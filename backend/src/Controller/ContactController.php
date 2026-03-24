<?php

namespace App\Controller;

use App\Application\Contact\SubmitContact\SubmitContactCommand;
use App\Application\Contact\SubmitContact\SubmitContactHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ContactController
{
    public function __construct(
        private SubmitContactHandler $handler,
        private ValidatorInterface $validator,
    ) {}

    #[Route('/api/contact', methods: ['POST'])]
    public function submit(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];

        $command = new SubmitContactCommand(
            name: trim((string) ($body['name'] ?? '')),
            email: trim((string) ($body['email'] ?? '')),
            message: trim((string) ($body['message'] ?? '')),
            phone: isset($body['phone']) ? trim((string) $body['phone']) : null,
            eventType: isset($body['eventType']) ? trim((string) $body['eventType']) : null,
            ipAddress: $request->getClientIp(),
        );

        $errors = $this->validateCommand($command);
        if (!empty($errors)) {
            return new JsonResponse(['errors' => $errors], 422);
        }

        $this->handler->handle($command);

        return new JsonResponse(['message' => 'Mensaje recibido. Nos pondremos en contacto pronto.'], 201);
    }

    private function validateCommand(SubmitContactCommand $cmd): array
    {
        $errors = [];

        if (empty($cmd->name)) {
            $errors['name'][] = 'El nombre es obligatorio';
        }
        if (empty($cmd->email)) {
            $errors['email'][] = 'El email es obligatorio';
        } elseif (!filter_var($cmd->email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'][] = 'El email no es válido';
        }
        if (empty($cmd->message)) {
            $errors['message'][] = 'El mensaje es obligatorio';
        } elseif (strlen($cmd->message) > 2000) {
            $errors['message'][] = 'El mensaje no puede superar los 2000 caracteres';
        }

        return $errors;
    }
}

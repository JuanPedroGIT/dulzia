<?php

namespace App\Controller;

use App\Application\Contact\SubmitContact\SubmitContactCommand;
use App\Application\Contact\SubmitContact\SubmitContactHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
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

        // Las reglas viven en el command (Assert); el ApiExceptionListener
        // convierte ValidationFailedException en 422 con los errores por campo.
        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($command, $violations);
        }

        $this->handler->handle($command);

        return new JsonResponse(['message' => 'Mensaje recibido. Nos pondremos en contacto pronto.'], 201);
    }
}

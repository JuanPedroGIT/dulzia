<?php

namespace App\Controller;

use App\Application\Settings\GetContactRecipient\GetContactRecipientHandler;
use App\Application\Settings\GetContactRecipient\GetContactRecipientQuery;
use App\Application\Settings\UpdateContactRecipient\UpdateContactRecipientCommand;
use App\Application\Settings\UpdateContactRecipient\UpdateContactRecipientHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Ajustes de la aplicación editables desde el panel admin.
 *
 * - Autenticación: AdminAuthListener (todo /api/admin excepto login).
 * - Errores: ApiExceptionListener (NotFoundException → 404, ValidationFailedException → 422).
 * - Sin lógica de negocio: solo deserializa, valida y responde.
 */
final class AdminSettingsController
{
    public function __construct(
        private GetContactRecipientHandler $getContactRecipient,
        private UpdateContactRecipientHandler $updateContactRecipient,
        private ValidatorInterface $validator,
    ) {}

    #[Route('/api/admin/settings/contact-recipient', methods: ['GET'])]
    public function getContactRecipient(): JsonResponse
    {
        return new JsonResponse($this->getContactRecipient->handle(new GetContactRecipientQuery()));
    }

    #[Route('/api/admin/settings/contact-recipient', methods: ['PUT'])]
    public function updateContactRecipient(Request $request): JsonResponse
    {
        $body = json_decode($request->getContent(), true);
        $body = is_array($body) ? $body : [];

        $command = new UpdateContactRecipientCommand(
            // Un PUT reemplaza el recurso entero: lo que no llega cuenta como
            // vacío, que es la forma de volver al valor del .env.
            email: is_string($body['email'] ?? null) ? $body['email'] : '',
            name: is_string($body['name'] ?? null) ? $body['name'] : '',
        );

        $violations = $this->validator->validate($command);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($command, $violations);
        }

        $this->updateContactRecipient->handle($command);

        return new JsonResponse(['ok' => true]);
    }
}

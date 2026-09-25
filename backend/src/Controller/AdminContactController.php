<?php

namespace App\Controller;

use App\Application\Contact\DeleteMessage\DeleteMessageCommand;
use App\Application\Contact\DeleteMessage\DeleteMessageHandler;
use App\Application\Contact\GetMessage\GetMessageHandler;
use App\Application\Contact\GetMessage\GetMessageQuery;
use App\Application\Contact\ListMessages\ListMessagesHandler;
use App\Application\Contact\ListMessages\ListMessagesQuery;
use App\Application\Contact\MarkMessageRead\MarkMessageReadCommand;
use App\Application\Contact\MarkMessageRead\MarkMessageReadHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Gestión de mensajes de contacto del panel admin.
 *
 * - Autenticación: AdminAuthListener (todo /api/admin excepto login).
 * - Errores: ApiExceptionListener (NotFoundException → 404, ValidationFailedException → 422).
 * - Sin lógica de negocio: solo deserializa, valida y responde.
 */
final class AdminContactController
{
    public function __construct(
        private ListMessagesHandler $listMessages,
        private GetMessageHandler $getMessage,
        private MarkMessageReadHandler $markMessageRead,
        private DeleteMessageHandler $deleteMessage,
        private ValidatorInterface $validator,
    ) {}

    #[Route('/api/admin/messages', methods: ['GET'])]
    public function listMessages(Request $request): JsonResponse
    {
        $query = new ListMessagesQuery(
            page: $request->query->getInt('page', 1),
            filter: $request->query->getString('filter', ListMessagesQuery::ALL),
        );

        $violations = $this->validator->validate($query);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($query, $violations);
        }

        return new JsonResponse($this->listMessages->handle($query));
    }

    #[Route('/api/admin/messages/{id}', methods: ['GET'])]
    public function getMessage(string $id): JsonResponse
    {
        $data = $this->getMessage->handle(new GetMessageQuery($id));

        if ($data === null) {
            return new JsonResponse(['error' => 'Mensaje no encontrado'], 404);
        }

        return new JsonResponse($data);
    }

    #[Route('/api/admin/messages/{id}/read', methods: ['POST'])]
    public function markRead(string $id): JsonResponse
    {
        $this->markMessageRead->handle(new MarkMessageReadCommand($id, true));

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/admin/messages/{id}/unread', methods: ['POST'])]
    public function markUnread(string $id): JsonResponse
    {
        $this->markMessageRead->handle(new MarkMessageReadCommand($id, false));

        return new JsonResponse(['ok' => true]);
    }

    #[Route('/api/admin/messages/{id}', methods: ['DELETE'])]
    public function deleteMessage(string $id): JsonResponse
    {
        $this->deleteMessage->handle(new DeleteMessageCommand($id));

        return new JsonResponse(['ok' => true]);
    }
}

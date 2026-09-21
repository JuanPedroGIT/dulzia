<?php

namespace App\EventListener;

use App\Domain\Shared\HttpMappableExceptionInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * Conversión centralizada de excepciones de dominio a respuestas JSON.
 * Los controllers NO capturan excepciones de dominio: todo se mapea aquí.
 *
 * Las excepciones de dominio implementan HttpMappableExceptionInterface y
 * se mapean solas (status + mensaje): este listener no cambia cuando se
 * añade un tipo nuevo (principio abierto/cerrado).
 */
final class ApiExceptionListener
{
    #[AsEventListener]
    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $exception = $event->getThrowable();

        if ($exception instanceof ValidationFailedException) {
            $errors = [];
            foreach ($exception->getViolations() as $violation) {
                $errors[$violation->getPropertyPath()][] = $violation->getMessage();
            }
            $event->setResponse(new JsonResponse(['errors' => $errors], 422));
            return;
        }

        if ($exception instanceof HttpMappableExceptionInterface) {
            $event->setResponse(new JsonResponse(
                ['error' => $exception->getMessage()],
                $exception->getStatusCode(),
            ));
            return;
        }

        if ($exception instanceof HttpExceptionInterface) {
            $event->setResponse(new JsonResponse(
                ['error' => $exception->getMessage()],
                $exception->getStatusCode(),
            ));
            return;
        }

        $event->setResponse(new JsonResponse(
            ['error' => 'Error interno del servidor'],
            500,
        ));
    }
}

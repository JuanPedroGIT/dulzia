<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionListener
{
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

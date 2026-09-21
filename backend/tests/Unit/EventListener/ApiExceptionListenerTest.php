<?php

declare(strict_types=1);

namespace App\Tests\Unit\EventListener;

use App\Domain\Admin\InvalidCredentialsException;
use App\Domain\Shared\HttpMappableExceptionInterface;
use App\Domain\Shared\NotFoundException;
use App\Domain\Storage\InvalidFileException;
use App\EventListener\ApiExceptionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class ApiExceptionListenerTest extends TestCase
{
    private function dispatch(\Throwable $exception, string $path = '/api/test'): ExceptionEvent
    {
        $kernel = $this->createMock(KernelInterface::class);
        $request = Request::create($path);
        $event = new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);

        (new ApiExceptionListener())->onKernelException($event);

        return $event;
    }

    public function testIgnoresNonApiRoutes(): void
    {
        $event = $this->dispatch(new \DomainException('x'), '/health');

        self::assertNull($event->getResponse());
    }

    public function testMapsValidationFailedTo422WithErrorsByField(): void
    {
        $violation = new ConstraintViolation(
            'El nombre es obligatorio',
            'El nombre es obligatorio',
            [],
            '',
            'name',
            '',
        );
        $exception = new ValidationFailedException('', new ConstraintViolationList([$violation]));

        $event = $this->dispatch($exception);

        $response = $event->getResponse();
        self::assertSame(422, $response->getStatusCode());
        self::assertSame(
            ['errors' => ['name' => ['El nombre es obligatorio']]],
            json_decode($response->getContent(), true),
        );
    }

    public function testMapsNotFoundExceptionTo404(): void
    {
        $event = $this->dispatch(new NotFoundException('Servicio no encontrado'));

        $response = $event->getResponse();
        self::assertSame(404, $response->getStatusCode());
        self::assertSame(
            ['error' => 'Servicio no encontrado'],
            json_decode($response->getContent(), true),
        );
    }

    public function testMapsInvalidFileExceptionTo400(): void
    {
        $event = $this->dispatch(new InvalidFileException('Tipo de archivo no permitido: text/plain'));

        $response = $event->getResponse();
        self::assertSame(400, $response->getStatusCode());
        self::assertSame(
            ['error' => 'Tipo de archivo no permitido: text/plain'],
            json_decode($response->getContent(), true),
        );
    }

    public function testMapsInvalidCredentialsTo401(): void
    {
        $event = $this->dispatch(new InvalidCredentialsException('Credenciales incorrectas'));

        $response = $event->getResponse();
        self::assertSame(401, $response->getStatusCode());
        self::assertSame(
            ['error' => 'Credenciales incorrectas'],
            json_decode($response->getContent(), true),
        );
    }

    public function testMapsHttpExceptionToItsStatusCode(): void
    {
        $event = $this->dispatch(new NotFoundHttpException('Ruta no encontrada'));

        $response = $event->getResponse();
        self::assertSame(404, $response->getStatusCode());
        self::assertSame(
            ['error' => 'Ruta no encontrada'],
            json_decode($response->getContent(), true),
        );
    }

    public function testMapsAnyDomainExceptionImplementingMappingInterface(): void
    {
        // OCP: una excepción de dominio NUEVA se mapea sola, sin tocar el listener
        $exception = new class('Conflicto de negocio') extends \RuntimeException implements HttpMappableExceptionInterface {
            public function getStatusCode(): int
            {
                return 409;
            }
        };

        $event = $this->dispatch($exception);

        $response = $event->getResponse();
        self::assertSame(409, $response->getStatusCode());
        self::assertSame(
            ['error' => 'Conflicto de negocio'],
            json_decode($response->getContent(), true),
        );
    }

    public function testMapsUnexpectedExceptionToGeneric500(): void
    {
        $event = $this->dispatch(new \RuntimeException('detalle interno'));

        $response = $event->getResponse();
        self::assertSame(500, $response->getStatusCode());
        // No se filtran detalles internos al cliente
        self::assertSame(
            ['error' => 'Error interno del servidor'],
            json_decode($response->getContent(), true),
        );
    }
}

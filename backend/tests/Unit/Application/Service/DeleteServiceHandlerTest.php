<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\DeleteService\DeleteServiceCommand;
use App\Application\Service\DeleteService\DeleteServiceHandler;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class DeleteServiceHandlerTest extends TestCase
{
    public function testDeactivatesServiceInsteadOfRemoving(): void
    {
        $service = TestFactory::service();
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save')->with($service);

        (new DeleteServiceHandler($repo))->handle(new DeleteServiceCommand('servicio-test'));

        self::assertFalse($service->isActive());
    }

    public function testThrowsWhenServiceNotFound(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->never())->method('save');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Servicio no encontrado');

        (new DeleteServiceHandler($repo))->handle(new DeleteServiceCommand('no-existe'));
    }
}

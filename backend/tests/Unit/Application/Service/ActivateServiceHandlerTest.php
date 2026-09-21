<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\Service;

use App\Application\Service\ActivateService\ActivateServiceCommand;
use App\Application\Service\ActivateService\ActivateServiceHandler;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Tests\Support\TestFactory;
use PHPUnit\Framework\TestCase;

final class ActivateServiceHandlerTest extends TestCase
{
    public function testActivatesService(): void
    {
        $service = TestFactory::service(isActive: false);
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn($service);
        $repo->expects($this->once())->method('save')->with($service);

        (new ActivateServiceHandler($repo))->handle(new ActivateServiceCommand('servicio-test'));

        self::assertTrue($service->isActive());
    }

    public function testThrowsWhenServiceNotFound(): void
    {
        $repo = $this->createMock(ServiceRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);
        $repo->expects($this->never())->method('save');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Servicio no encontrado');

        (new ActivateServiceHandler($repo))->handle(new ActivateServiceCommand('no-existe'));
    }
}

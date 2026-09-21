<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\AdminAuth;

use App\Application\AdminAuth\Login\LoginCommand;
use App\Application\AdminAuth\Login\LoginHandler;
use App\Domain\Admin\AdminTokenStoreInterface;
use App\Domain\Admin\AdminUserRepositoryInterface;
use App\Domain\Admin\InvalidCredentialsException;
use PHPUnit\Framework\TestCase;

final class LoginHandlerTest extends TestCase
{
    private function handler(AdminUserRepositoryInterface $admins, AdminTokenStoreInterface $tokens): LoginHandler
    {
        return new LoginHandler($admins, $tokens);
    }

    public function testReturnsTokenWithValidCredentials(): void
    {
        $hash = password_hash('secreta', PASSWORD_BCRYPT);

        $admins = $this->createMock(AdminUserRepositoryInterface::class);
        $admins->expects($this->once())
            ->method('findPasswordHash')
            ->with('admin')
            ->willReturn($hash);

        $tokens = $this->createMock(AdminTokenStoreInterface::class);
        $tokens->method('create')->willReturn('token-123');

        $token = $this->handler($admins, $tokens)->handle(new LoginCommand('admin', 'secreta'));

        self::assertSame('token-123', $token);
    }

    public function testThrowsWithWrongPassword(): void
    {
        $admins = $this->createMock(AdminUserRepositoryInterface::class);
        $admins->method('findPasswordHash')
            ->willReturn(password_hash('secreta', PASSWORD_BCRYPT));

        $tokens = $this->createMock(AdminTokenStoreInterface::class);
        $tokens->expects($this->never())->method('create');

        $this->expectException(InvalidCredentialsException::class);
        $this->expectExceptionMessage('Credenciales incorrectas');

        $this->handler($admins, $tokens)->handle(new LoginCommand('admin', 'incorrecta'));
    }

    public function testThrowsWithUnknownUser(): void
    {
        $admins = $this->createMock(AdminUserRepositoryInterface::class);
        $admins->method('findPasswordHash')->willReturn(null);

        $tokens = $this->createMock(AdminTokenStoreInterface::class);
        $tokens->expects($this->never())->method('create');

        $this->expectException(InvalidCredentialsException::class);

        $this->handler($admins, $tokens)->handle(new LoginCommand('no-existe', 'secreta'));
    }
}

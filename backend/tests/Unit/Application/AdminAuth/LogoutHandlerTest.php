<?php

declare(strict_types=1);

namespace App\Tests\Unit\Application\AdminAuth;

use App\Application\AdminAuth\Logout\LogoutCommand;
use App\Application\AdminAuth\Logout\LogoutHandler;
use App\Domain\Admin\AdminTokenStoreInterface;
use PHPUnit\Framework\TestCase;

final class LogoutHandlerTest extends TestCase
{
    public function testDeletesAllTokens(): void
    {
        $tokens = $this->createMock(AdminTokenStoreInterface::class);
        $tokens->expects($this->once())->method('deleteAll');

        (new LogoutHandler($tokens))->handle(new LogoutCommand());
    }
}

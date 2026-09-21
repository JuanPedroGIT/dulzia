<?php

namespace App\Application\AdminAuth\Logout;

use App\Domain\Admin\AdminTokenStoreInterface;

final class LogoutHandler
{
    public function __construct(
        private AdminTokenStoreInterface $tokens,
    ) {}

    public function handle(LogoutCommand $command): void
    {
        $this->tokens->deleteAll();
    }
}

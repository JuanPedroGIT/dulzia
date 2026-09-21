<?php

namespace App\Application\AdminAuth\Login;

use App\Domain\Admin\AdminTokenStoreInterface;
use App\Domain\Admin\AdminUserRepositoryInterface;
use App\Domain\Admin\InvalidCredentialsException;

final class LoginHandler
{
    public function __construct(
        private AdminUserRepositoryInterface $admins,
        private AdminTokenStoreInterface $tokens,
    ) {}

    public function handle(LoginCommand $command): string
    {
        $hash = $this->admins->findPasswordHash($command->username);

        if ($hash === null || !password_verify($command->password, $hash)) {
            throw new InvalidCredentialsException('Credenciales incorrectas');
        }

        return $this->tokens->create();
    }
}

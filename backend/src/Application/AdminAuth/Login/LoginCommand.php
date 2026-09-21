<?php

namespace App\Application\AdminAuth\Login;

final readonly class LoginCommand
{
    public function __construct(
        public string $username,
        public string $password,
    ) {}
}

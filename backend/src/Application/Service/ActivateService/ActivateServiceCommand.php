<?php

namespace App\Application\Service\ActivateService;

final readonly class ActivateServiceCommand
{
    public function __construct(
        public string $id,
    ) {}
}

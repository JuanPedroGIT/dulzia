<?php

namespace App\Application\Service\DeleteService;

final readonly class DeleteServiceCommand
{
    public function __construct(
        public string $id,
    ) {}
}

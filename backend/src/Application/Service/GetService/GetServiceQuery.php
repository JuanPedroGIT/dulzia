<?php

namespace App\Application\Service\GetService;

final readonly class GetServiceQuery
{
    public function __construct(
        public string $id,
    ) {}
}

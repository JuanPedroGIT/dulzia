<?php

namespace App\Application\Service\GetCatalogService;

final readonly class GetCatalogServiceQuery
{
    public function __construct(
        public string $id,
    ) {}
}

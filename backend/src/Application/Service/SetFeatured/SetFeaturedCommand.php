<?php

namespace App\Application\Service\SetFeatured;

final readonly class SetFeaturedCommand
{
    public function __construct(
        public string $id,
        public bool $featured,
    ) {}
}

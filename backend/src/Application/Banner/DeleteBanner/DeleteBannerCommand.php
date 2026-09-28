<?php

namespace App\Application\Banner\DeleteBanner;

final readonly class DeleteBannerCommand
{
    public function __construct(
        public string $id,
    ) {}
}

<?php

namespace App\Application\Banner\ListBanners;

use App\Domain\Banner\BannerRepositoryInterface;

final class ListBannersHandler
{
    public function __construct(
        private BannerRepositoryInterface $banners,
    ) {}

    /** @return array<int, array<string, mixed>> Todos, de más reciente a más antiguo. */
    public function handle(ListBannersQuery $query): array
    {
        return array_map(
            static fn ($banner) => $banner->toArray(),
            $this->banners->findAll(),
        );
    }
}

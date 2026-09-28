<?php

namespace App\Application\Banner\GetActiveBanner;

use App\Domain\Banner\BannerRepositoryInterface;

final class GetActiveBannerHandler
{
    public function __construct(
        private BannerRepositoryInterface $banners,
    ) {}

    /**
     * @return array{banner: array<string, mixed>|null}
     */
    public function handle(GetActiveBannerQuery $query): array
    {
        $banner = $this->banners->findActiveOn(
            $query->date ?? new \DateTimeImmutable('today'),
        );

        return ['banner' => $banner?->toArray()];
    }
}

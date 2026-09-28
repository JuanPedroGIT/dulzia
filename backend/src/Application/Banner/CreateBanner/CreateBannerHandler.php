<?php

namespace App\Application\Banner\CreateBanner;

use App\Application\Banner\BannerDate;
use App\Domain\Banner\BannerRepositoryInterface;
use App\Entity\Banner;

final class CreateBannerHandler
{
    public function __construct(
        private BannerRepositoryInterface $banners,
    ) {}

    public function handle(CreateBannerCommand $command): array
    {
        $banner = new Banner(
            title: $command->title,
            description: $command->description,
            startsAt: BannerDate::parse($command->startsAt),
            endsAt: BannerDate::parse($command->endsAt),
        );

        $this->banners->save($banner);

        return $banner->toArray();
    }
}

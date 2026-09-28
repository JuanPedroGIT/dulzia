<?php

namespace App\Application\Banner\UpdateBanner;

use App\Application\Banner\BannerDate;
use App\Domain\Banner\BannerRepositoryInterface;
use App\Domain\Shared\NotFoundException;

final class UpdateBannerHandler
{
    public function __construct(
        private BannerRepositoryInterface $banners,
    ) {}

    public function handle(UpdateBannerCommand $command): void
    {
        $banner = $this->banners->find($command->id);

        if ($banner === null) {
            throw new NotFoundException('Banner no encontrado');
        }

        $banner->update(
            title: $command->title,
            description: $command->description,
            startsAt: BannerDate::parse($command->startsAt),
            endsAt: BannerDate::parse($command->endsAt),
        );

        $this->banners->save($banner);
    }
}

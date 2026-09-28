<?php

namespace App\Application\Banner\DeleteBanner;

use App\Domain\Banner\BannerRepositoryInterface;
use App\Domain\Shared\NotFoundException;

final class DeleteBannerHandler
{
    public function __construct(
        private BannerRepositoryInterface $banners,
    ) {}

    public function handle(DeleteBannerCommand $command): void
    {
        $banner = $this->banners->find($command->id);

        if ($banner === null) {
            throw new NotFoundException('Banner no encontrado');
        }

        $this->banners->delete($banner);
    }
}

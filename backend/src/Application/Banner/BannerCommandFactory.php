<?php

namespace App\Application\Banner;

use App\Application\Banner\CreateBanner\CreateBannerCommand;
use App\Application\Banner\UpdateBanner\UpdateBannerCommand;
use Symfony\Component\HttpFoundation\Request;

/**
 * Convierte el body de las peticiones de banners en comandos. El panel manda
 * JSON, así que el parseo es el mismo en alta y edición; los campos ausentes
 * llegan vacíos y los pilla el NotBlank del command (→ 422).
 */
final class BannerCommandFactory
{
    public function createFromRequest(Request $request): CreateBannerCommand
    {
        $data = $this->parse($request);

        return new CreateBannerCommand(
            title: $data['title'],
            description: $data['description'],
            startsAt: $data['starts_at'],
            endsAt: $data['ends_at'],
        );
    }

    public function updateFromRequest(string $id, Request $request): UpdateBannerCommand
    {
        $data = $this->parse($request);

        return new UpdateBannerCommand(
            id: $id,
            title: $data['title'],
            description: $data['description'],
            startsAt: $data['starts_at'],
            endsAt: $data['ends_at'],
        );
    }

    /**
     * @return array{title: string, description: string, starts_at: string, ends_at: string}
     */
    private function parse(Request $request): array
    {
        $body = json_decode($request->getContent(), true);
        $body = is_array($body) ? $body : [];

        return [
            'title'       => is_string($body['title'] ?? null) ? $body['title'] : '',
            'description' => is_string($body['description'] ?? null) ? $body['description'] : '',
            'starts_at'   => is_string($body['starts_at'] ?? null) ? $body['starts_at'] : '',
            'ends_at'     => is_string($body['ends_at'] ?? null) ? $body['ends_at'] : '',
        ];
    }
}

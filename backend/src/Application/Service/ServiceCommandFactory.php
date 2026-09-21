<?php

namespace App\Application\Service;

use App\Application\Service\CreateService\CreateServiceCommand;
use App\Application\Service\UpdateService\UpdateServiceCommand;
use App\Domain\Shared\InvalidInputException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Convierte el body JSON de las peticiones admin en comandos de servicio.
 * El parseo era idéntico en create y update: centralizado aquí para que
 * los controllers no repitan lógica.
 */
final class ServiceCommandFactory
{
    public function createFromRequest(Request $request): CreateServiceCommand
    {
        $data = $this->parse($request);

        return new CreateServiceCommand(
            name: $data['name'],
            emoji: $data['emoji'],
            description: $data['description'],
            features: $data['features'],
            category: $data['category'],
        );
    }

    public function updateFromRequest(string $id, Request $request): UpdateServiceCommand
    {
        $data = $this->parse($request);

        return new UpdateServiceCommand(
            id: $id,
            name: $data['name'],
            emoji: $data['emoji'],
            description: $data['description'],
            features: $data['features'],
            category: $data['category'],
        );
    }

    /** @return array{name: string, emoji: string, description: string, features: string[], category: string} */
    private function parse(Request $request): array
    {
        $body = json_decode($request->getContent(), true) ?? [];

        $name = trim($body['name'] ?? '');
        $emoji = trim($body['emoji'] ?? '');
        $description = trim($body['description'] ?? '');

        if ($name === '' || $emoji === '' || $description === '') {
            // ApiExceptionListener lo convierte en 400
            throw new InvalidInputException('name, emoji y description son requeridos');
        }

        return [
            'name' => $name,
            'emoji' => $emoji,
            'description' => $description,
            'features' => array_values(array_filter(array_map('trim', $body['features'] ?? []))),
            'category' => $body['category'] ?? 'food',
        ];
    }
}

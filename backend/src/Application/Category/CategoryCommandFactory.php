<?php

namespace App\Application\Category;

use App\Application\Category\CreateCategory\CreateCategoryCommand;
use App\Application\Category\UpdateCategory\UpdateCategoryCommand;
use App\Domain\Shared\InvalidInputException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Convierte el body de las peticiones de categorías en comandos. El panel manda
 * JSON (no hay ficheros), así que el parseo es el mismo en alta y edición.
 */
final class CategoryCommandFactory
{
    public function createFromRequest(Request $request): CreateCategoryCommand
    {
        $data = $this->parse($request);

        return new CreateCategoryCommand(
            name: $data['name'],
            emoji: $data['emoji'],
            sortOrder: $data['sort_order'],
        );
    }

    public function updateFromRequest(string $id, Request $request): UpdateCategoryCommand
    {
        $data = $this->parse($request);

        return new UpdateCategoryCommand(
            id: $id,
            name: $data['name'],
            emoji: $data['emoji'],
            sortOrder: $data['sort_order'],
        );
    }

    /**
     * @return array{name: string, emoji: string|null, sort_order: int}
     */
    private function parse(Request $request): array
    {
        $body = json_decode($request->getContent(), true) ?? [];

        $name = trim((string) ($body['name'] ?? ''));
        $emoji = trim((string) ($body['emoji'] ?? ''));

        if ($name === '') {
            // ApiExceptionListener lo convierte en 400
            throw new InvalidInputException('name es requerido');
        }

        return [
            'name' => $name,
            // Cadena vacía = sin emoji (las pestañas del catálogo lo omiten).
            'emoji' => $emoji !== '' ? $emoji : null,
            'sort_order' => (int) ($body['sort_order'] ?? 0),
        ];
    }
}

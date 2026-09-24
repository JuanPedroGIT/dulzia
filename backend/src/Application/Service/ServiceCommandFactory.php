<?php

namespace App\Application\Service;

use App\Application\Service\CreateService\CreateServiceCommand;
use App\Application\Service\UpdateService\UpdateServiceCommand;
use App\Domain\Shared\InvalidInputException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Convierte el body de las peticiones admin en comandos de servicio.
 * El panel envía multipart (adjunta la foto de la sección en la misma petición);
 * la API sigue aceptando JSON. El parseo era idéntico en create y update:
 * centralizado aquí para que los controllers no repitan lógica.
 */
final class ServiceCommandFactory
{
    public function createFromRequest(Request $request): CreateServiceCommand
    {
        $data = $this->parse($request);

        return new CreateServiceCommand(
            name: $data['name'],
            // En el alta el emoji es NOT NULL en BD: cadena vacía si no llega.
            emoji: $data['emoji'] ?? '',
            description: $data['description'],
            features: $data['features'],
            category: $data['category'],
            image: $request->files->get('image'),
            thumbnail: $request->files->get('thumbnail'),
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
            image: $request->files->get('image'),
            thumbnail: $request->files->get('thumbnail'),
            removeImage: $data['removeImage'],
        );
    }

    /**
     * En multipart (el panel) los campos llegan en `$request->request` y
     * `features` como `features[]`; en JSON, en el cuerpo crudo.
     *
     * @return array{name: string, emoji: string|null, description: string, features: string[], category: string, removeImage: bool}
     */
    private function parse(Request $request): array
    {
        $body = $request->getContentTypeFormat() === 'json'
            ? (json_decode($request->getContent(), true) ?? [])
            : $request->request->all();

        $name = trim($body['name'] ?? '');
        $description = trim($body['description'] ?? '');
        $emoji = trim($body['emoji'] ?? '');

        if ($name === '' || $description === '') {
            // ApiExceptionListener lo convierte en 400
            throw new InvalidInputException('name y description son requeridos');
        }

        return [
            'name' => $name,
            // El panel ya no edita el emoji: null = conservar el actual, que se
            // mantiene como último respaldo visual de las secciones.
            'emoji' => $emoji !== '' ? $emoji : null,
            'description' => $description,
            'features' => array_values(array_filter(array_map('trim', $body['features'] ?? []))),
            'category' => $body['category'] ?? 'food',
            'removeImage' => filter_var($body['removeImage'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ];
    }
}

<?php

namespace App\Application\Service\UpdateService;

use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Domain\Storage\FileStorageInterface;

final class UpdateServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
        private FileStorageInterface $storage,
    ) {}

    public function handle(UpdateServiceCommand $command): void
    {
        $service = $this->services->findById($command->id);

        if ($service === null) {
            throw new NotFoundException('Servicio no encontrado');
        }

        $service->update(
            name:        $command->name,
            // El panel ya no manda emoji: se conserva el que tuviera de respaldo.
            emoji:       $command->emoji ?? $service->getEmoji(),
            description: $command->description,
            features:    $command->features,
            category:    $command->category,
        );

        // Foto de la sección: subir la nueva o quitarla.
        $current = $service->getImageUrl();
        $changesImage = $command->image !== null || $command->removeImage;

        if ($command->image !== null) {
            $service->setImageUrl($this->storage->store($command->image));
        } elseif ($command->removeImage) {
            $service->setImageUrl(null);
        }

        $this->services->save($service);

        // La foto anterior se borra después de guardar: si el guardado falla, la
        // BD sigue apuntando a ella y el fichero tiene que seguir existiendo.
        // Un borrado fallido deja un objeto huérfano, que es mucho más barato
        // que una imagen rota.
        if ($changesImage && $current !== null) {
            $this->storage->delete($current);
        }
    }
}

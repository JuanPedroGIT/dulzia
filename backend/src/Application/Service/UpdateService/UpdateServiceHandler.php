<?php

namespace App\Application\Service\UpdateService;

use App\Application\Service\CategoryResolver;
use App\Domain\Service\ServiceRepositoryInterface;
use App\Domain\Shared\NotFoundException;
use App\Domain\Storage\FileStorageInterface;

final class UpdateServiceHandler
{
    public function __construct(
        private ServiceRepositoryInterface $services,
        private FileStorageInterface $storage,
        private CategoryResolver $categoryResolver,
    ) {}

    public function handle(UpdateServiceCommand $command): void
    {
        $service = $this->services->findById($command->id);

        if ($service === null) {
            throw new NotFoundException('Servicio no encontrado');
        }

        // Falla con 400 si la categoría no existe (no hay clave foránea).
        $category = $this->categoryResolver->resolve($command->category);

        $service->update(
            name:        $command->name,
            // El panel ya no manda emoji: se conserva el que tuviera de respaldo.
            emoji:       $command->emoji ?? $service->getEmoji(),
            description: $command->description,
            features:    $command->features,
            category:    $category->getId(),
        );

        // Foto de la sección: subir la nueva o quitarla.
        $current = $service->getImageUrl();
        $currentThumbnail = $service->getThumbnailUrl();
        $changesImage = $command->image !== null || $command->removeImage;

        if ($command->image !== null) {
            $service->setImageUrl($this->storage->store($command->image));
            $service->setThumbnailUrl(
                $command->thumbnail !== null ? $this->storage->store($command->thumbnail) : null
            );
        } elseif ($command->removeImage) {
            $service->setImageUrl(null);
            $service->setThumbnailUrl(null);
        }

        $this->services->save($service);

        // La foto anterior se borra después de guardar: si el guardado falla, la
        // BD sigue apuntando a ella y el fichero tiene que seguir existiendo.
        // Un borrado fallido deja un objeto huérfano, que es mucho más barato
        // que una imagen rota. La miniatura es un objeto aparte: se borra también.
        if ($changesImage) {
            if ($current !== null) {
                $this->storage->delete($current);
            }
            if ($currentThumbnail !== null) {
                $this->storage->delete($currentThumbnail);
            }
        }
    }
}

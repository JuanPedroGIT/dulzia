<?php

declare(strict_types=1);

namespace App\Tests\Support;

use App\Entity\ContactSubmission;
use App\Entity\Service;
use App\Entity\ServiceExample;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Factories de fixtures para tests unitarios y de integración.
 */
final class TestFactory
{
    /** PNG 1x1 real — finfo lo detecta como image/png */
    private const PNG_1PX = 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    public static function service(
        string $id = 'servicio-test',
        string $name = 'Servicio Test',
        string $emoji = '🎉',
        string $description = 'Descripción del servicio',
        array $features = [],
        string $category = 'food',
        int $sortOrder = 0,
        bool $isActive = true,
        ?string $imageUrl = null,
    ): Service {
        $service = new Service(
            id:          $id,
            name:        $name,
            emoji:       $emoji,
            description: $description,
            features:    $features,
            category:    $category,
            imageUrl:    $imageUrl,
            sortOrder:   $sortOrder,
        );

        if (!$isActive) {
            $service->deactivate();
        }

        return $service;
    }

    public static function example(
        Service $service,
        string $title = 'Foto de prueba',
        string $description = 'Descripción de la foto',
        string $imageUrl = 'https://fake-storage.test/services/abc.jpg',
        int $sortOrder = 0,
    ): ServiceExample {
        return new ServiceExample($service, $title, $description, $imageUrl, $sortOrder);
    }

    /**
     * Añade examples a un Service sin Doctrine: los tests unitarios no hydratan
     * la colección (solo la rellena el ORM).
     */
    public static function attachExamples(Service $service, ServiceExample ...$examples): void
    {
        $reflection = new \ReflectionProperty(Service::class, 'examples');
        $reflection->setValue($service, new ArrayCollection($examples));
    }

    public static function contactSubmission(
        string $name = 'María',
        string $email = 'maria@example.com',
        string $message = 'Hola, quiero un presupuesto',
        ?string $phone = '+34 600 000 000',
        ?string $eventType = 'boda',
        ?string $ipAddress = '127.0.0.1',
    ): ContactSubmission {
        return new ContactSubmission($name, $email, $message, $phone, $eventType, $ipAddress);
    }

    /**
     * UploadedFile válido respaldado por un PNG real de 1 píxel (finfo lo reconoce).
     */
    public static function uploadedFile(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'dulzia-test-');
        file_put_contents($path, base64_decode(self::PNG_1PX, true));

        return new UploadedFile($path, 'foto.png', 'image/png', null, true);
    }

    /**
     * UploadedFile con contenido que NO es una imagen.
     * Symfony 7.3 ignora el MIME declarado y lo adivina del contenido (finfo),
     * así que el guard de MIME solo se dispara con un archivo real no-imagen.
     */
    public static function nonImageFile(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'dulzia-test-');
        file_put_contents($path, "<?php echo 'esto no es una imagen';");

        return new UploadedFile($path, 'archivo.txt', null, null, true);
    }

    /**
     * UploadedFile inválido (error de subida) para probar los guards.
     */
    public static function invalidUploadedFile(): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'dulzia-test-');
        file_put_contents($path, base64_decode(self::PNG_1PX, true));

        return new UploadedFile($path, 'foto.png', 'image/png', UPLOAD_ERR_NO_FILE, false);
    }
}

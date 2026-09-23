<?php

namespace App\Domain\Settings;

use Psr\Log\LoggerInterface;

/**
 * Resuelve a quién se envían las notificaciones de contacto: lo configurado en
 * el panel (tabla `settings`) y, si ahí no hay nada, el valor del .env.
 *
 * Se resuelve en cada envío y no al construir el contenedor, para que un cambio
 * en el panel se aplique sin reiniciar nada.
 */
final class ContactRecipientResolver
{
    public function __construct(
        private SettingsRepositoryInterface $settings,
        private LoggerInterface $logger,
        private string $defaultEmail,
        private string $defaultName,
    ) {}

    public function resolve(): ContactRecipient
    {
        try {
            $email = $this->settings->get(SettingKey::CONTACT_RECIPIENT_EMAIL);
            $name = $this->settings->get(SettingKey::CONTACT_RECIPIENT_NAME);
        } catch (\Throwable $e) {
            // La tabla puede no existir todavía (migración pendiente) o fallar
            // la BD. Preferimos seguir enviando al destinatario del .env que
            // dejar al negocio sin los mensajes del formulario.
            $this->logger->error('No se pudieron leer los ajustes; se usa el valor por defecto', [
                'exception' => $e,
            ]);

            $email = $name = null;
        }

        return new ContactRecipient(
            email: $email ?? $this->defaultEmail,
            name: $name ?? $this->defaultName,
            emailFromSettings: $email !== null,
            nameFromSettings: $name !== null,
        );
    }
}

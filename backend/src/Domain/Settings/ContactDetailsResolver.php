<?php

namespace App\Domain\Settings;

use Psr\Log\LoggerInterface;

/**
 * Lee los datos de contacto publicados en la web (tabla `settings`). A
 * diferencia de ContactRecipientResolver no hay valor por defecto en el
 * servidor: si en el panel no hay nada, devuelve `null` y es la web la que
 * enseña el suyo. El respaldo del email del mailer sí vive aquí porque un
 * envío sin destinatario no se puede hacer; el de un teléfono que solo se
 * pinta, sí.
 */
final class ContactDetailsResolver
{
    public function __construct(
        private SettingsRepositoryInterface $settings,
        private LoggerInterface $logger,
    ) {}

    public function resolve(): ContactDetails
    {
        try {
            $email = $this->settings->get(SettingKey::CONTACT_EMAIL);
            $phone = $this->settings->get(SettingKey::CONTACT_PHONE);
        } catch (\Throwable $e) {
            // La tabla puede no existir todavía (migración pendiente) o fallar la
            // BD. Preferimos que la web siga funcionando con sus valores de
            // siempre antes que dejar las páginas sin datos de contacto.
            $this->logger->error('No se pudieron leer los datos de contacto; la web usará los suyos', [
                'exception' => $e,
            ]);

            $email = $phone = null;
        }

        return new ContactDetails(
            email: $email,
            phone: $phone,
            emailFromSettings: $email !== null,
            phoneFromSettings: $phone !== null,
        );
    }
}

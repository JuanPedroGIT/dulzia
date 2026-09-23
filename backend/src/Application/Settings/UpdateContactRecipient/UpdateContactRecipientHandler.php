<?php

namespace App\Application\Settings\UpdateContactRecipient;

use App\Domain\Settings\SettingKey;
use App\Domain\Settings\SettingsRepositoryInterface;

final class UpdateContactRecipientHandler
{
    public function __construct(
        private SettingsRepositoryInterface $settings,
    ) {}

    public function handle(UpdateContactRecipientCommand $command): void
    {
        // Los valores ya llegan normalizados: el Command recorta al construirse.
        $this->apply(SettingKey::CONTACT_RECIPIENT_EMAIL, $command->email);
        $this->apply(SettingKey::CONTACT_RECIPIENT_NAME, $command->name);
    }

    private function apply(string $key, string $value): void
    {
        // Vaciar el campo es la forma de volver al valor del .env: si no hay
        // fila, el resolver cae al valor por defecto.
        if ($value === '') {
            $this->settings->delete($key);

            return;
        }

        $this->settings->set($key, $value);
    }
}

<?php

namespace App\Application\Settings\UpdateContactDetails;

use App\Domain\Settings\SettingKey;
use App\Domain\Settings\SettingsRepositoryInterface;

final class UpdateContactDetailsHandler
{
    public function __construct(
        private SettingsRepositoryInterface $settings,
    ) {}

    public function handle(UpdateContactDetailsCommand $command): void
    {
        // Los valores ya llegan normalizados: el Command recorta al construirse.
        $this->apply(SettingKey::CONTACT_EMAIL, $command->email);
        $this->apply(SettingKey::CONTACT_PHONE, $command->phone);
    }

    private function apply(string $key, string $value): void
    {
        // Vaciar el campo es la forma de volver al valor por defecto de la web:
        // si no hay fila, el resolver devuelve null y el frontend usa el suyo.
        if ($value === '') {
            $this->settings->delete($key);

            return;
        }

        $this->settings->set($key, $value);
    }
}

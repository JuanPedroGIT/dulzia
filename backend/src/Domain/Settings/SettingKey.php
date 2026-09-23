<?php

namespace App\Domain\Settings;

/**
 * Claves de la tabla `settings`. Única fuente de verdad: si el literal se
 * escribiera a mano en cada sitio, una errata en uno solo haría que el ajuste
 * "desapareciera" en silencio y se volviera al valor del .env sin que nadie
 * se entere.
 */
final class SettingKey
{
    public const CONTACT_RECIPIENT_EMAIL = 'contact_recipient_email';
    public const CONTACT_RECIPIENT_NAME = 'contact_recipient_name';

    private function __construct() {}
}

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

    // Datos que se PUBLICAN en la web (pie, contacto, legales, JSON-LD). Son
    // claves distintas de las del destinatario a propósito: el destinatario es
    // interno (a dónde llegan los avisos) y publicarlo sería filtrar una
    // dirección que no tiene por qué ser la de cara al público.
    public const CONTACT_EMAIL = 'contact_email';
    public const CONTACT_PHONE = 'contact_phone';

    private function __construct() {}
}

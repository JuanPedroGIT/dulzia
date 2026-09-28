<?php

namespace App\Application\Banner\GetActiveBanner;

/**
 * El banner visible ahora mismo. `date` queda en null para "hoy": los tests
 * la fijan para probar la ventana sin depender del reloj.
 */
final readonly class GetActiveBannerQuery
{
    public function __construct(
        public ?\DateTimeImmutable $date = null,
    ) {}
}

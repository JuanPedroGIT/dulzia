<?php

namespace App\Application\Banner;

use App\Domain\Shared\InvalidInputException;

/**
 * Fechas de la ventana del banner: llegan como "AAAA-MM-DD" (las valida el
 * command) y aquí se convierten a día a medianoche.
 */
final class BannerDate
{
    public static function parse(string $date): \DateTimeImmutable
    {
        // `!` descarta la hora actual y fija medianoche: el banner vive en días.
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);

        if ($parsed === false) {
            throw new InvalidInputException("La fecha \"$date\" no es válida");
        }

        return $parsed;
    }
}

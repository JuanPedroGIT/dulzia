<?php

namespace App\Application\Shared;

/**
 * Política de identificadores: del nombre a un slug ASCII.
 * `Animación infantil` → `animacion-infantil`.
 *
 * La comparten los generadores de ids de servicios y de categorías, para que un
 * nombre se convierta igual en los dos sitios.
 */
final class Slug
{
    /**
     * Acentos y eñes al slug. Sin esto, el patrón de limpieza convierte cualquier
     * letra no ASCII en un guion y `Animación` daría `animaci-n`. Se incluyen las
     * mayúsculas porque strtolower() de PHP es byte a byte y no las convierte.
     */
    private const ACCENTS = [
        'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u',
        'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
        'â' => 'a', 'ê' => 'e', 'î' => 'i', 'ô' => 'o', 'û' => 'u',
        'ç' => 'c', 'ñ' => 'n',
        'Á' => 'a', 'É' => 'e', 'Í' => 'i', 'Ó' => 'o', 'Ú' => 'u', 'Ü' => 'u',
        'À' => 'a', 'È' => 'e', 'Ì' => 'i', 'Ò' => 'o', 'Ù' => 'u',
        'Â' => 'a', 'Ê' => 'e', 'Î' => 'i', 'Ô' => 'o', 'Û' => 'u',
        'Ç' => 'c', 'Ñ' => 'n',
    ];

    public static function fromName(string $name): string
    {
        $slug = strtr(strtolower($name), self::ACCENTS);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim($slug, '-');
    }
}

<?php

namespace App\Application\Category\ListCategories;

final readonly class ListCategoriesQuery
{
    /**
     * El panel necesita saber cuántas secciones usan cada categoría (para avisar
     * antes de borrar); la web pública no.
     */
    public function __construct(
        public bool $withServiceCount = false,
    ) {}
}

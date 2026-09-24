<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924220000 extends AbstractMigration
{
    /** Las 3 categorías que hasta ahora estaban escritas a mano en el frontend. */
    private const SEED = [
        ['food', 'Gastronomía', '🍴', 0],
        ['decoration', 'Decoración', '🎨', 1],
        ['experience', 'Experiencias', '✨', 2],
    ];

    public function getDescription(): string
    {
        return 'Crear la tabla category y sembrarla con las categorías existentes';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE category (
                id VARCHAR(50) NOT NULL,
                name VARCHAR(100) NOT NULL,
                emoji VARCHAR(20) DEFAULT NULL,
                sort_order INT NOT NULL DEFAULT 0,
                PRIMARY KEY(id)
            )
        SQL);

        foreach (self::SEED as [$id, $name, $emoji, $sortOrder]) {
            $this->addSql(
                'INSERT INTO category (id, name, emoji, sort_order) VALUES (?, ?, ?, ?)',
                [$id, $name, $emoji, $sortOrder],
            );
        }

        // Si algún servicio usara una categoría que no está en la semilla, se da de
        // alta para que ninguna sección quede apuntando a algo que no existe.
        //
        // No hay clave foránea a propósito: el esquema se deriva de los mapeos de
        // Doctrine (que no pueden declarar una FK sobre una columna suelta), así que
        // una FK puesta a mano aquí la borraría la próxima `migration-diff`. La
        // integridad se garantiza en los handlers (validación al asignar y bloqueo
        // al borrar una categoría en uso).
        $this->addSql(<<<'SQL'
            INSERT INTO category (id, name, sort_order)
            SELECT DISTINCT s.category, s.category, 99
            FROM service s
            WHERE s.category IS NOT NULL
              AND NOT EXISTS (SELECT 1 FROM category c WHERE c.id = s.category)
        SQL);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE category');
    }
}

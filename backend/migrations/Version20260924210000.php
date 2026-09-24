<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924210000 extends AbstractMigration
{
    /**
     * Los 6 servicios que el hero de la portada tenía escritos a mano en el
     * código (HeroSection.vue). Se marcan aquí para que al desplegar la portada
     * siga saliendo igual y no se quede sin tarjetas.
     */
    private const HERO_SERVICES = [
        'carrito-hot-dog',
        'glitter-bar',
        'fuente-chocolate',
        'candy-bar',
        'photocall',
        'mini-ferias',
    ];

    public function getDescription(): string
    {
        return 'Añadir is_featured a service (destacados de la portada) y marcar los del hero';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service ADD is_featured BOOLEAN NOT NULL DEFAULT FALSE');

        foreach (self::HERO_SERVICES as $id) {
            $this->addSql('UPDATE service SET is_featured = TRUE WHERE id = ?', [$id]);
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service DROP is_featured');
    }
}

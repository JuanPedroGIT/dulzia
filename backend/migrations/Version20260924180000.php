<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924180000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añadir image_url a service (foto de la sección en el panel)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service ADD image_url VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service DROP image_url');
    }
}

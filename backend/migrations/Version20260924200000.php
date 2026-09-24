<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260924200000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Añadir thumbnail_url a service y service_example (miniaturas de las fotos)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service ADD thumbnail_url VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE service_example ADD thumbnail_url VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE service DROP thumbnail_url');
        $this->addSql('ALTER TABLE service_example DROP thumbnail_url');
    }
}

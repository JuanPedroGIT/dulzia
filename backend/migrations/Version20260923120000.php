<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create settings table (key/value app settings, e.g. contact notification recipient)';
    }

    public function up(Schema $schema): void
    {
        // La clave es la PK: el índice único que necesitamos es el de la propia PK.
        // `key` y `value` no son palabras reservadas en PostgreSQL, así que van sin comillas.
        $this->addSql('CREATE TABLE settings (key VARCHAR(100) NOT NULL, value TEXT NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY (key))');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE settings');
    }
}

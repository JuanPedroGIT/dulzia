<?php
declare(strict_types=1);
namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create admin_user and admin_token tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE admin_user (
            id SERIAL PRIMARY KEY,
            username VARCHAR(100) NOT NULL UNIQUE,
            password_hash VARCHAR(255) NOT NULL
        )');

        $this->addSql('CREATE TABLE admin_token (
            id SERIAL PRIMARY KEY,
            token VARCHAR(128) NOT NULL UNIQUE,
            expires_at TIMESTAMP NOT NULL
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE admin_token');
        $this->addSql('DROP TABLE admin_user');
    }
}

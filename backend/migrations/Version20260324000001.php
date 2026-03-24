<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260324000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create contact_submission table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE contact_submission (
            id VARCHAR(36) NOT NULL,
            name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) DEFAULT NULL,
            event_type VARCHAR(100) DEFAULT NULL,
            message TEXT NOT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            submitted_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
            email_sent BOOLEAN NOT NULL DEFAULT FALSE,
            email_sent_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE contact_submission');
    }
}

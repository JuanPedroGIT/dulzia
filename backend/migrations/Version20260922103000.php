<?php

declare(strict_types=1);

namespace App\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922103000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add read_at to contact_submission (admin messages management)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact_submission ADD read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE contact_submission DROP read_at');
    }
}

<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260426210000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'event: add persons, sources, location, country columns for OSINT sidebar';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE event ADD persons JSON DEFAULT NULL, ADD sources JSON DEFAULT NULL, ADD location VARCHAR(200) DEFAULT NULL, ADD country VARCHAR(10) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE event DROP persons, DROP sources, DROP location, DROP country');
    }
}

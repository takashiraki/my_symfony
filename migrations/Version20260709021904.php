<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260709021904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment_source ADD COLUMN created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE payment_source ADD COLUMN updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__payment_source AS SELECT id, name, type, user_id FROM payment_source');
        $this->addSql('DROP TABLE payment_source');
        $this->addSql('CREATE TABLE payment_source (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, user_id INTEGER NOT NULL, CONSTRAINT FK_7AB2E4E5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO payment_source (id, name, type, user_id) SELECT id, name, type, user_id FROM __temp__payment_source');
        $this->addSql('DROP TABLE __temp__payment_source');
        $this->addSql('CREATE INDEX IDX_7AB2E4E5A76ED395 ON payment_source (user_id)');
    }
}

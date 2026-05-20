<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520023637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(255) NOT NULL, country VARCHAR(255) NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL)');
        $this->addSql('ALTER TABLE task ADD COLUMN created_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE task ADD COLUMN updated_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE user');
        $this->addSql('CREATE TEMPORARY TABLE __temp__task AS SELECT id, value, description, due_date, status FROM task');
        $this->addSql('DROP TABLE task');
        $this->addSql('CREATE TABLE task (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, value VARCHAR(255) NOT NULL, description VARCHAR(255) DEFAULT NULL, due_date DATETIME DEFAULT NULL, status VARCHAR(255) NOT NULL)');
        $this->addSql('INSERT INTO task (id, value, description, due_date, status) SELECT id, value, description, due_date, status FROM __temp__task');
        $this->addSql('DROP TABLE __temp__task');
    }
}

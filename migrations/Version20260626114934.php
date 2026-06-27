<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260626114934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE money_diary ADD COLUMN type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TEMPORARY TABLE __temp__money_diary AS SELECT id, title, date, amount, category_id, payment_source_id, user_id FROM money_diary');
        $this->addSql('DROP TABLE money_diary');
        $this->addSql('CREATE TABLE money_diary (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, date DATE NOT NULL, amount INTEGER NOT NULL, category_id INTEGER DEFAULT NULL, payment_source_id INTEGER DEFAULT NULL, user_id INTEGER DEFAULT NULL, CONSTRAINT FK_FF0F4E212469DE2 FOREIGN KEY (category_id) REFERENCES category (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FF0F4E2D6153349 FOREIGN KEY (payment_source_id) REFERENCES payment_source (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_FF0F4E2A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO money_diary (id, title, date, amount, category_id, payment_source_id, user_id) SELECT id, title, date, amount, category_id, payment_source_id, user_id FROM __temp__money_diary');
        $this->addSql('DROP TABLE __temp__money_diary');
        $this->addSql('CREATE INDEX IDX_FF0F4E212469DE2 ON money_diary (category_id)');
        $this->addSql('CREATE INDEX IDX_FF0F4E2D6153349 ON money_diary (payment_source_id)');
        $this->addSql('CREATE INDEX IDX_FF0F4E2A76ED395 ON money_diary (user_id)');
    }
}

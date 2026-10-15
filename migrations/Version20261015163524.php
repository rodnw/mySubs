<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261015163524 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Schéma initial : inventory, member, subwoofer';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inventory (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) DEFAULT NULL)');
        $this->addSql('CREATE TABLE member (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, inventory_id INTEGER NOT NULL, CONSTRAINT FK_70E4FA789EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70E4FA789EEA759 ON member (inventory_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON member (email)');
        $this->addSql('CREATE TABLE subwoofer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, model VARCHAR(255) NOT NULL, brand VARCHAR(255) NOT NULL, year VARCHAR(255) NOT NULL, inventory_id INTEGER NOT NULL, CONSTRAINT FK_53652B569EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_53652B569EEA759 ON subwoofer (inventory_id)');
        $this->addSql('CREATE TABLE messenger_messages (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, body CLOB NOT NULL, headers CLOB NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL)');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 ON messenger_messages (queue_name, available_at, delivered_at, id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE inventory');
        $this->addSql('DROP TABLE member');
        $this->addSql('DROP TABLE subwoofer');
        $this->addSql('DROP TABLE messenger_messages');
    }
}

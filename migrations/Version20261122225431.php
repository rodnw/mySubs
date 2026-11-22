<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261122225431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Collections renommées en sound_system, nom de profil des membres, image des caissons et vitrines (showcase)';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE showcase (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, title VARCHAR(255) NOT NULL, description CLOB DEFAULT NULL, published BOOLEAN NOT NULL, creator_id INTEGER NOT NULL, CONSTRAINT FK_14B88CD061220EA6 FOREIGN KEY (creator_id) REFERENCES member (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_14B88CD061220EA6 ON showcase (creator_id)');
        $this->addSql('CREATE TABLE showcase_subwoofer (showcase_id INTEGER NOT NULL, subwoofer_id INTEGER NOT NULL, PRIMARY KEY (showcase_id, subwoofer_id), CONSTRAINT FK_570B8396B9441CED FOREIGN KEY (showcase_id) REFERENCES showcase (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_570B839645D8FFDF FOREIGN KEY (subwoofer_id) REFERENCES subwoofer (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_570B8396B9441CED ON showcase_subwoofer (showcase_id)');
        $this->addSql('CREATE INDEX IDX_570B839645D8FFDF ON showcase_subwoofer (subwoofer_id)');
        $this->addSql('CREATE TABLE sound_system (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) DEFAULT NULL)');
        $this->addSql('INSERT INTO sound_system (id, description) SELECT id, description FROM inventory');
        $this->addSql('DROP TABLE inventory');
        $this->addSql('CREATE TEMPORARY TABLE __temp__member AS SELECT id, email, roles, password, inventory_id FROM member');
        $this->addSql('DROP TABLE member');
        $this->addSql('CREATE TABLE member (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, sound_system_id INTEGER NOT NULL, name VARCHAR(100) NOT NULL, CONSTRAINT FK_70E4FA78E28DFB18 FOREIGN KEY (sound_system_id) REFERENCES sound_system (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO member (id, email, roles, password, sound_system_id, name) SELECT id, email, roles, password, inventory_id, substr(email, 1, instr(email || \'@\', \'@\') - 1) FROM __temp__member');
        $this->addSql('DROP TABLE __temp__member');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON member (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70E4FA78E28DFB18 ON member (sound_system_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__subwoofer AS SELECT id, model, brand, year, inventory_id FROM subwoofer');
        $this->addSql('DROP TABLE subwoofer');
        $this->addSql('CREATE TABLE subwoofer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, model VARCHAR(255) NOT NULL, brand VARCHAR(255) NOT NULL, year VARCHAR(255) NOT NULL, sound_system_id INTEGER NOT NULL, image_name VARCHAR(255) DEFAULT NULL, image_size INTEGER DEFAULT NULL, updated_at DATETIME DEFAULT NULL, CONSTRAINT FK_53652B56E28DFB18 FOREIGN KEY (sound_system_id) REFERENCES sound_system (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO subwoofer (id, model, brand, year, sound_system_id) SELECT id, model, brand, year, inventory_id FROM __temp__subwoofer');
        $this->addSql('DROP TABLE __temp__subwoofer');
        $this->addSql('CREATE INDEX IDX_53652B56E28DFB18 ON subwoofer (sound_system_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE inventory (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, description VARCHAR(255) DEFAULT NULL COLLATE "BINARY")');
        $this->addSql('INSERT INTO inventory (id, description) SELECT id, description FROM sound_system');
        $this->addSql('DROP TABLE showcase');
        $this->addSql('DROP TABLE showcase_subwoofer');
        $this->addSql('CREATE TEMPORARY TABLE __temp__member AS SELECT id, email, roles, password, sound_system_id FROM member');
        $this->addSql('DROP TABLE member');
        $this->addSql('CREATE TABLE member (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, email VARCHAR(180) NOT NULL, roles CLOB NOT NULL, password VARCHAR(255) NOT NULL, inventory_id INTEGER NOT NULL, CONSTRAINT FK_70E4FA789EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO member (id, email, roles, password, inventory_id) SELECT id, email, roles, password, sound_system_id FROM __temp__member');
        $this->addSql('DROP TABLE __temp__member');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON member (email)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_70E4FA789EEA759 ON member (inventory_id)');
        $this->addSql('CREATE TEMPORARY TABLE __temp__subwoofer AS SELECT id, model, brand, year, sound_system_id FROM subwoofer');
        $this->addSql('DROP TABLE subwoofer');
        $this->addSql('CREATE TABLE subwoofer (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, model VARCHAR(255) NOT NULL, brand VARCHAR(255) NOT NULL, year VARCHAR(255) NOT NULL, inventory_id INTEGER NOT NULL, CONSTRAINT FK_53652B569EEA759 FOREIGN KEY (inventory_id) REFERENCES inventory (id) ON UPDATE NO ACTION ON DELETE NO ACTION NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('INSERT INTO subwoofer (id, model, brand, year, inventory_id) SELECT id, model, brand, year, sound_system_id FROM __temp__subwoofer');
        $this->addSql('DROP TABLE __temp__subwoofer');
        $this->addSql('CREATE INDEX IDX_53652B569EEA759 ON subwoofer (inventory_id)');
        $this->addSql('DROP TABLE sound_system');
    }
}

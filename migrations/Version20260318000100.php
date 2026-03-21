<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260318000100 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create users and notes tables for account confirmation and note management.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE app_user (
            id SERIAL NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_verified BOOLEAN NOT NULL,
            confirmation_token VARCHAR(64) DEFAULT NULL,
            confirmation_token_expires_at TIMESTAMP DEFAULT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE UNIQUE INDEX uniq_app_user_email ON app_user (email)');
        $this->addSql('CREATE UNIQUE INDEX uniq_app_user_confirmation_token ON app_user (confirmation_token)');

        $this->addSql('CREATE TABLE note (
            id SERIAL NOT NULL,
            owner_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content TEXT NOT NULL,
            category VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL,
            created_at TIMESTAMP NOT NULL,
            updated_at TIMESTAMP NOT NULL,
            PRIMARY KEY(id)
        )');

        $this->addSql('CREATE INDEX idx_note_status ON note (status)');
        $this->addSql('CREATE INDEX idx_note_category ON note (category)');
        $this->addSql('CREATE INDEX idx_note_created_at ON note (created_at)');
        $this->addSql('CREATE INDEX idx_note_owner ON note (owner_id)');
        $this->addSql('ALTER TABLE note ADD CONSTRAINT fk_note_owner FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE note DROP CONSTRAINT fk_note_owner');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE app_user');
    }
}

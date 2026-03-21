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
            id INT AUTO_INCREMENT NOT NULL,
            email VARCHAR(180) NOT NULL,
            roles JSON NOT NULL,
            password VARCHAR(255) NOT NULL,
            is_verified TINYINT(1) NOT NULL,
            confirmation_token VARCHAR(64) DEFAULT NULL,
            confirmation_token_expires_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            UNIQUE INDEX uniq_app_user_email (email),
            UNIQUE INDEX uniq_app_user_confirmation_token (confirmation_token),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('CREATE TABLE note (
            id INT AUTO_INCREMENT NOT NULL,
            owner_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            content LONGTEXT NOT NULL,
            category VARCHAR(100) NOT NULL,
            status VARCHAR(20) NOT NULL,
            created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
            INDEX idx_note_status (status),
            INDEX idx_note_category (category),
            INDEX idx_note_created_at (created_at),
            INDEX IDX_NOTE_OWNER (owner_id),
            PRIMARY KEY(id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql('ALTER TABLE note ADD CONSTRAINT FK_NOTE_OWNER FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE note DROP FOREIGN KEY FK_NOTE_OWNER');
        $this->addSql('DROP TABLE note');
        $this->addSql('DROP TABLE app_user');
    }
}
